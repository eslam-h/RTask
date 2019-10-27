<?php

namespace App\Http\Controllers\Web\TourGuideLanguageController;

use App\Http\Controllers\Abstracts\AbstractWebController;
use Dev\Domain\Entity\TourGuideLanguage;
use Dev\Domain\Service\TourGuideLanguageService\TourGuideLanguageService;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Application\Exceptions\NotFoundException;
use Illuminate\Http\Request;
use Validator;

/**
 * TourGuideController Class responsible for tour guides actions
 * @package App\Http\Controllers\Web\TourGuideController
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class TourGuideLanguageController extends AbstractWebController
{
    /**
     * @var TourGuideLanguageService $tourGuideLanguageService
     */
    private $tourGuideLanguageService;

    /**
     * @var int page count
     */
    private $count = 30;

    /**
     * TourGuideLanguageController constructor.
     * @param TourGuideLanguageService $tourGuideLanguageService
     * @param Request $request
     */
    public function __construct(Request $request, TourGuideLanguageService $tourGuideLanguageService)
    {
        parent::__construct($request);
        $this->tourGuideLanguageService = $tourGuideLanguageService;
    }

    /**
     * display tour guide language create form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayTourGuideLanguageCreationForm()
    {
        $actionUrl = "/tour-guide-language/create";
        $data = [
            "actionUrl" => $actionUrl
        ];
        return view("front.tour-guide-language.tour-guide-language-form", $data);
    }

    /**
     * create a new tour guide language action
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function createNewTourLanguageGuideAction(Request $request)
    {
        $messages = [
            "language.required" => "The language name field is required.",
            "language.unique" => "The language name must be unique.",
            "languageCode.unique" => "The language code must be unique.",
            "languageCode.required" => "The language code field is required.",
        ];
        $validator = Validator::make($request->all(), [
            "language" => "required|unique:tour-guide-language,language",
            "languageCode" => "required|unique:tour-guide-language,language-code",
        ], $messages);
        if ($validator->fails()) {
            return redirect('tour-guide-language/create')
                ->withErrors($validator)
                ->withInput();
        }
        $data = $request->all();
        $languageItem = $this->mapDataToTourGuideLanguageEntity($data);
        try {
            $this->tourGuideLanguageService->addNewTourGuideLanguage($languageItem);
        } catch (InvalidArgumentException $argumentException) {
        }
        return redirect("tour-guide-language/list");
    }

    /**
     * get all saved tour guide languages in the database
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listTourGuidesLanguages(Request $request)
    {
        $data = $request->all();
        $criteria = [];

        $page         = $request->has('page')? abs((int) $request->get('page')) : 1;
        $count        = $request->has('limit')? abs((int) $request->get('limit')) : $this->count;
        $offset       = ($page - 1) * $count;

        if (isset($data["name-search"])) {
            $criteria["language-filter"] = $data["name-search"];
        }

        $languages = $this->tourGuideLanguageService->getTourGuideLanguagesWithCriteria($criteria, $count, $offset);
        $paging = $this->tourGuideLanguageService->getTourGuideLanguagesLinks($criteria, $count, $offset);
        $data = [
            "entities" => $languages,
            "paging" => $paging,
            "searchInput" => isset($data['name-search']) ? $data['name-search'] : ''
        ];

        return view("front.tour-guide-language.list", $data);
    }

    /**
     * display tour guide language edit form
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function displayTourGuideLanguageEditForm($id)
    {
        try{
            $languageItem = $this->tourGuideLanguageService->getTourGuideLanguageWithId($id);
        }catch (NotFoundException $otFoundException ){
            return redirect("currency/list");
        }
        $actionUrl = "/tour-guide-language/$id/update";
        $data = [
            "language" => $languageItem->language,
            "languageCode" => $languageItem->languageCode,
            "actionUrl" => $actionUrl,
        ];
        return view("front.tour-guide-language.tour-guide-language-form", $data);
    }

    /**
     * edit and update tour guide language action
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateTourGuideLanguageAction(Request $request, $id)
    {
        try{
            $languageItem =$this->tourGuideLanguageService->getTourGuideLanguageWithId($id);
        }catch (NotFoundException $notFoundException){
            return redirect("tour-guide-language/list");
        }
        $messages = [
            "language.required" => "The language name field is required.",
            "languageCode.required" => "The language code field is required.",
            "language.unique" => "The language name must be unique.",
            "languageCode.unique" => "The language code must be unique.",
        ];
        $validator = Validator::make($request->all(), [
            "language" => "required|unique:tour-guide-language,language,".$languageItem->id,
            "languageCode" => "required|unique:tour-guide-language,language-code,".$languageItem->id,
        ], $messages);

        if ($validator->fails()) {
            return redirect("/tour-guide-language/{$id}/edit")
                ->withErrors($validator)
                ->withInput();
        }
        $data = $request->all();
        $language = new TourGuideLanguage();
        $language->id = $id;
        $language->language = $data['language'];
        $language->languageCode = $data['languageCode'];

        try{
            $this->tourGuideLanguageService->updateTourGuideLanguage($language);
        }catch (InvalidArgumentException $invalidArgumentException){
            return redirect("/tour-guide-language/{$id}/edit")
                ->withErrors($validator)
                ->withInput();
        }
        return redirect("tour-guide-language/list");
    }

    /**
     * delete tour guide language action
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteTourGuideLanguageAction($id)
    {
        $this->tourGuideLanguageService->deleteTourGuideLanguage($id);
        return redirect("tour-guide-language/list");
    }

    /**
     * Map request data to @see TourGuideLanguage instance
     * @param array $data
     * @return TourGuideLanguage
     */
    private function mapDataToTourGuideLanguageEntity(array $data) : TourGuideLanguage
    {
        $tourGuideLanguage = new TourGuideLanguage();
        if (isset($data["language"])) {
            $tourGuideLanguage->language = $data["language"];
        }
        if (isset($data["languageCode"])) {
            $tourGuideLanguage->languageCode = $data["languageCode"];
        }
        return $tourGuideLanguage;
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
        $tourGuideLanguagesWithCriteria = $this->tourGuideLanguageService->getTourGuideLanguagesWithCriteria($criteria, 1, $offset + $paging->count);
        if (empty($tourGuideLanguagesWithCriteria)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }
}