<?php

namespace App\Http\Controllers\Web\CityController;

use App\Utility\UploadPaths;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Application\Exceptions\NotFoundException;
use App\Http\Controllers\Abstracts\AbstractWebController;
use Dev\Domain\Entity\City;
use Dev\Domain\Entity\CityTag;
use Dev\Domain\Entity\CityTranslation;
use Dev\Domain\Entity\Country;
use Dev\Domain\Entity\Tag;
use Dev\Domain\Service\CityService\CityService;
use Dev\Domain\Service\CityService\CityTagService;
use Dev\Domain\Service\CityService\CityTranslationService;
use Dev\Domain\Service\CountryService\CountryService;
use Dev\Domain\Service\SystemLanguageService\SystemAvailableLanguageService;
use Dev\Domain\Service\TagService\TagService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Validator;
use App\Helpers\Helper;

/**
 * Class CityController responsible for all actions related to city
 * @package App\Http\Controllers\Web\CityController
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class CityController extends AbstractWebController
{
    /**
     * @var CityService $cityService instance from CityService
     */
    private $cityService;

    /**
     * @var int page count
     */
    private $count = 30;

    /**
     * @var  CityTagService $cityTagService instance from CityTagService
     */
    private $cityTagService;

    /**
     * @var CityTranslationService $cityTranslationService instance from City translation service
     */
    private $cityTranslationService;

    /**
     * @var SystemAvailableLanguageService $systemAvailableLanguageService instance from System available languages service
     */
    private $systemAvailableLanguageService;

    /**
     * @var CountryService $countrySerive instance from CountryService
     */
    private $countryService;

    /**
     * @var TagService $tagsService instance from Tags service
     */
    private $tagsService;

    /**
     * CityController constructor.
     * @param CityService $cityService
     * @param CountryService $countryService
     * @param CityTranslationService $cityTranslationService
     * @param TagService $tagService
     * @param SystemAvailableLanguageService $systemAvailableLanguageService
     * @param CityTagService $cityTagService
     * @param Request $request
     */
    public function __construct(
        Request $request,
        CityService $cityService,
        CountryService $countryService,
        CityTranslationService $cityTranslationService,
        TagService $tagService,
        SystemAvailableLanguageService $systemAvailableLanguageService,
        CityTagService $cityTagService
    ) {
        parent::__construct($request);
        $this->cityService = $cityService;
        $this->cityTranslationService = $cityTranslationService;
        $this->tagsService = $tagService;
        $this->systemAvailableLanguageService = $systemAvailableLanguageService;
        $this->countryService= $countryService;
        $this->cityTagService= $cityTagService;
    }

    /**
     * display city create form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayCityCreationForm()
    {
        $actionUrl = "/city/create";
        $availableLanguages = $this->systemAvailableLanguageService->getAllAvailableLanguages();
        $allAvailableLanguages = [];
        foreach ($availableLanguages as $availableLanguage) {
            $allAvailableLanguages[$availableLanguage->systemLanguage->code] = $availableLanguage->systemLanguage->language;
        }
        $countries = $this->getAllCountries();
        $tags = $this->tagsService->getAllTags();
        $data = [
            "actionUrl" => $actionUrl,
            "countries" => $countries,
            "tags" => $tags,
            "availableLanguages" => $allAvailableLanguages
        ];
        return view("front.city.city-form", $data);
    }

    /**
     * create a new city action
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function createNewCityAction(Request $request)
    {
        $messages = [
            "default-name.required" => "The default name field is required.",
            "default-name.unique" => "The City default name must be unique.",
            "country.required" => "The Country field is required.",
            "tag.required" => "The Tag field is required.",
            "image.required" => "The City Image is required.",
            "image.mimes" => "The City Image must be of type jpeg, jpg or png."
        ];
        $validator = Validator::make($request->all(), [
            "default-name" => "required|unique:cities,name",
            "image" => "required|image|mimes:jpeg,png",
            "country" => "required",
            "tag.*" => "required"
        ], $messages);
        if ($validator->fails()) {
            return redirect('city/create')
                ->withErrors($validator)
                ->withInput();
        }
        $data = $request->all();
        $city = $this->mapDataToCityEntity($data);
        try {
            $createdCity = $this->cityService->addNewCity($city);
        } catch (InvalidArgumentException $argumentException) {
            dd('err');
        }
        $photoPath = $request->file("image")->store(UploadPaths::CITY_IMAGES_PATH . "/{$createdCity->id}");
        $cityWithPhotoPatch = new City();
        $cityWithPhotoPatch->id = $createdCity->id;
        $cityWithPhotoPatch->image = $photoPath;
        try{
            $this->cityService->updateCity($cityWithPhotoPatch);
        }catch (InvalidArgumentException $invalidArgumentException){
        }
        Helper::resizeImageAll($photoPath);
        /**
         * @var CityTranslation $cityTranslation
         */
        if (!empty($city->cityTranslation)){
            foreach ($city->cityTranslation as $cityTranslation) {
                $cityTranslation->city = $createdCity;
                try{
                    $this->cityTranslationService->addNewCityTranslation($cityTranslation);
                }
                catch (InvalidArgumentException $invalidArgumentException){
                    continue;
                }
            }
        }
        /**
         * var CityTag $tag
         */
        if (!empty($city->tags)){
            foreach ($city->tags as $cityTag) {
                $tag = new Tag();
                $tag->id = $cityTag->tag->id;
                $cityTag = new CityTag();
                $cityTag->tag = $tag;
                $cityTag->city = $createdCity;
                try{
                    $this->cityTagService->addNewCityTag($cityTag);
                }
                catch (InvalidArgumentException $invalidArgumentException){
                    continue;
                }
            }
        }
        return redirect("city/list");
    }

    /**
     * listing all cities
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listCities(Request $request)
    {
        $data = $request->all();
        $criteria = [];

        $page         = $request->has('page')? abs((int) $request->get('page')) : 1;
        $count        = $request->has('limit')? abs((int) $request->get('limit')) : $this->count;
        $offset       = ($page - 1) * $count;

        if (isset($data["name-search"])) {
            $criteria["name-filter"] = $data["name-search"];
        }

        $cities = $this->cityService->getCitiesWithCriteria($criteria, $count, $offset);
//        $paging = $this->getPaging($criteria, $count, $offset);
        $paging = $this->cityService->getPaginationLinks($criteria, $count, $offset);

        $data = [
            "entities" => $cities,
            "paging" => $paging,
            "searchInput" => isset($data['name-search']) ? $data['name-search'] : ''
        ];

        return view("front.city.list", $data);
    }

    /**
     * delete city action
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteCityAction($id)
    {
        try {
            $this->cityService->deleteCity($id);
        } catch (QueryException $queryException) {
            switch ($queryException->getCode()) {
                case 23000:
                    $message = "City can not be deleted as it is being used by another instance in the system";
                    return redirect("city/list")->with("delete-city-error", $message);
                    break;
            }
        }
        return redirect("city/list");
    }

    /**
     * display city edit form
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayCityEditForm($id)
    {
        try{
            $cityItem = $this->cityService->getCityWithId($id);
        }catch (NotFoundException $otFoundException ){
            return redirect("city/list");
        }
        $criteria['city-id']=$id;
        $cityItemTranslation = $this->cityTranslationService->getCitiesTranslationWithCriteria($criteria);
        $availableLanguages = $this->systemAvailableLanguageService->getAllAvailableLanguages();
        $allAvailableLanguages = [];
        foreach ($availableLanguages as $availableLanguage) {
            $allAvailableLanguages[$availableLanguage->systemLanguage->code] = $availableLanguage->systemLanguage->language;
        }
        $name = [];
        foreach ($cityItemTranslation as $cityTranslationItem)
        {
            $languageCriteria['code'] = $cityTranslationItem->code;
            $activityLanguageName = $this->systemAvailableLanguageService->getLanguageWithCriteria($languageCriteria);
            $name[] = [
                "code" => $cityTranslationItem->code,
                "value" => $cityTranslationItem->name,
                "language" => $activityLanguageName[0]->systemLanguage->language
            ];
        }
        $currentCityTagsIds = [];
        foreach ($cityItem->tags as $tag){
            $currentCityTagsIds[] = $tag->tag->id;
        }

        $cityCountry = $this->countryService->getCityWithId($cityItem->country->id);
        $actionUrl = "/city/$id/update";
        $countries = $this->getAllCountries();
        $tags = $this->tagsService->getAllTags();
        $imageName = substr($cityItem->image, strrpos($cityItem->image, $id.'/'));

        $data = [
            "defaultName" => $cityItem->name,
            "countries" => $countries ? $countries :[],
            "tags" => $tags ? $tags :[],
            "imageName" => $imageName,
            "cityTranslation" => $name ? $name : [],
            "image" => $cityItem->image,
            "country" => $cityCountry ? $cityCountry : [],
            "tag" => $currentCityTagsIds ,
            "actionUrl" => $actionUrl,
            "availableLanguages" => $allAvailableLanguages ? $allAvailableLanguages :[],
        ];
        return view("front.city.city-form", $data);
    }

    /**
     * update city action
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateCityAction(Request $request, $id)
    {
        try{
            $cityItem = $this->cityService->getCityWithId($id);
        }catch (NotFoundException $notFoundException){
            return redirect("city/list");
        }
        $messages = [
            "default-name.required" => "The default name field is required.",
            'default-name.unique' => 'The default name field must be unique.',
            "country.required" => "The Country field is required.",
            "tag.*.required" => "The City Tag is required.",
            "image.required" => "The City Image is required.",
            "image.mimes" => "The City Image must be of type jpeg, jpg or png."
        ];
        $validator = Validator::make($request->all(), [
            "default-name" => "required|unique:cities,name,".$cityItem->id,
            "country" => "required|exists:countries,id",
            "tag.*" => "required|exists:tags,id"
        ], $messages);

        if(empty($cityItem->image)){
            $validator = Validator::make($request->all(), [
                "image" => "required|image|mimes:jpeg,jpg,png",
            ], $messages);
        }
        if ($validator->fails()) {
            return redirect('/city/'.$id.'/edit')
                ->withErrors($validator)
                ->withInput();
        }
        $currentCityTagsIds = [];
        foreach ($cityItem->tags as $tag){
            $currentCityTagsIds[] = $tag->tag->id;
        }
        $data = $request->all();
        $city = $this->mapDataToCityEntity($data);
        $city->id = $id;

        if(isset($data['image'])){
            $city->image = $request->file("image")->store(UploadPaths::CITY_IMAGES_PATH . "/{$id}");
        }
        try{
            $this->cityService->updateCity($city);
        }catch (InvalidArgumentException $invalidArgumentException){
        }

        /**
         * @var CityTranslation $cityTranslation
         */
        if (!empty($city->cityTranslation)){
            foreach ($city->cityTranslation as $cityTranslation)
            {
                $criteria = [
                    "city-id" => $id,
                    "language-code" => $cityTranslation->code
                ];
                $cityTagItem = $this->cityTranslationService->getCitiesTranslationWithCriteria($criteria);
                if ($cityTagItem) {
                    $activityTranslationItemId = $cityTagItem[0]->id;
                    if ($cityTranslation->name)
                    {
                        $cityTranslation->id = $activityTranslationItemId;
                        try{
                            $this->cityTranslationService->updateCityTranslation($cityTranslation);
                        }
                        catch (InvalidArgumentException $invalidArgumentException){
                        }
                    } else {
                        $this->cityTranslationService->deleteCityTranslation($activityTranslationItemId);
                    }
                } else {
                    $cityTranslation->city = new City();
                    $cityTranslation->city->id = $city->id;
                    try{
                        $this->cityTranslationService->addNewCityTranslation($cityTranslation);
                    }
                    catch (InvalidArgumentException $invalidArgumentException){
                        continue;
                    }
                }
            }
        }
        $selectedCityTagsIds = [];
        if (!empty($city->tags)){
            foreach ($city->tags as $cityTag)
            {
                $selectedCityTagsIds[] = $cityTag->tag->id;
            }
            $tobeDeleted = array_diff($currentCityTagsIds, $selectedCityTagsIds);
            foreach ($tobeDeleted as $tagId){
                $criteria=[
                    "tag-id"=> $tagId,
                    "city-id" => $id
                ];
                $this->cityTagService->deleteWithCriteria($criteria);
            }
            $tobeAdded = array_diff($selectedCityTagsIds, $currentCityTagsIds);
            foreach ($tobeAdded as $tagId){
                $tag = new Tag();
                $tag->id = $tagId;
                $cityTag = new CityTag();
                $cityTag->tag = $tag;
                $cityTag->city = $city;
                try{
                    $this->cityTagService->addNewCityTag($cityTag);

                }catch (InvalidArgumentException $invalidArgumentException){

                }
            }
        }
        return redirect("city/list");
    }


    /**
     * @return array
     */
    private function getAllCountries() : array
    {
        $countries = $this->countryService->getAllCountries();
        $entities = [];
        /**
         * @var Country $country
         */
        foreach ($countries as $country) {
            $entities[$country->id] = $country->name;
        }
        return $entities;
    }


    /**
     * map data to city entity array
     * @param array $data
     * @return City
     */
    private function mapDataToCityEntity(array $data) : City
    {
        $city = new City();
        if (isset($data["default-name"])) {
            $city->name = $data["default-name"];
        }
        if (isset($data["city-translation"]) && !empty($data["city-translation"])) {
            foreach ($data["city-translation"] as $langCode => $nameTranslation) {
                $cityTranslation = new CityTranslation();
                $cityTranslation->name = $nameTranslation;
                $cityTranslation->code = $langCode;
                $city->cityTranslation[] = $cityTranslation;
            }
        }
        if (isset($data["image"])) {
            $city->image = $data["image"];
        }
        if (isset($data["country"])) {
            $country = new Country();
            $country->id = $data['country'];
            $city->country = $country;
        }
        if (isset($data["tag"])) {
            foreach ($data['tag'] as $tagId){
                $tag = new Tag();
                $tag->id = $tagId;
                $cityTag = new CityTag();
                $cityTag->tag = $tag;
                $city->tags[] = $cityTag;
            }
        }
        return $city;
    }

    /**
     * @param array $criteria
     * @param int|null $count
     * @param int|null $offset
     * @return \stdClass
     */
    private function getPaging(array $criteria, int $count = null, int $offset = null)
    {
        $paging = parent::initializePaging($count, $offset);
        $citiesWithCriteria = $this->cityService->getCitiesWithCriteria($criteria, 1, $offset + $paging->count);
        if (empty($citiesWithCriteria)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }
}
