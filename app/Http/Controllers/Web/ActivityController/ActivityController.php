<?php

namespace App\Http\Controllers\Web\ActivityController;

use App\Http\Controllers\Abstracts\AbstractWebController;
use App\Http\Requests\ActivityRequest\ActivityPostRequest;
use App\Utility\Paging;
use App\Utility\UploadPaths;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Application\Exceptions\NotFoundException;
use Dev\Domain\Entity\Activity;
use Dev\Domain\Entity\ActivityTranslation;
use Dev\Domain\Service\ActivityService\ActivityService;
use Dev\Domain\Service\ActivityService\ActivityTranslationService;
use Dev\Domain\Service\SystemLanguageService\SystemAvailableLanguageService;
use Dev\Infrastructure\Models\ActivityModels\ActivityModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Validator;

/**
 * ActivityController Class responsible for all actions related to activity
 * @package App\Http\Controllers\Web\ActivityController
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class ActivityController extends AbstractWebController
{
    /**
     * @var ActivityService $activityService instance from ActivityService
     */
    private $activityService;

    /**
     * @var SystemAvailableLanguageService $systemAvailableLanguageService instance from System available languages service
     */
    private $systemAvailableLanguageService;

    /**
     * @var int page count
     */
    private $count = 30;
    /**
     * @var ActivityTranslationService $activityTranslationService instance from Activity translation service
     */
    private $activityTranslationService;

    /**
     * ActivityController constructor.
     * @param ActivityService $activityService
     * @param SystemAvailableLanguageService $availableLanguageService
     * @param ActivityTranslationService $activityTranslationService
     * @param Request $request
     */
    public function __construct(
        Request $request,
        ActivityService $activityService,
        SystemAvailableLanguageService $availableLanguageService,
        ActivityTranslationService $activityTranslationService
    ) {
        parent::__construct($request);
        $this->activityService = $activityService;
        $this->systemAvailableLanguageService = $availableLanguageService;
        $this->activityTranslationService = $activityTranslationService;
    }

    /**
     * display activity create form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayActivityCreationForm()
    {
        $actionUrl = "/activity/create";
        $availableLanguages = $this->systemAvailableLanguageService->getAllAvailableLanguages();
        $allAvailableLanguages = [];
        foreach ($availableLanguages as $availableLanguage) {
            $allAvailableLanguages[$availableLanguage->systemLanguage->code] = $availableLanguage->systemLanguage->language;
        }

        $data = [
            "actionUrl" => $actionUrl,
            "availableLanguages" => $allAvailableLanguages
        ];
        return view("front.activity.activity-form", $data);
    }

    /**
     * update activity creation
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Dev\Application\Exceptions\InvalidArgumentException
     */
    public function updateActivityAction(Request $request, $id)
    {
        try {
            $activityItem = $this->activityService->getActivityWithId($id);
        } catch (NotFoundException $notFoundException) {
            return redirect("activity/list");
        }
        $messages = [
            'default-name.required' => 'The default name field is required.',
            'default-name.unique' => 'The default name field must be unique.',
            'photo.required' => 'The activity background photo is required.',
            'icon.required' => 'The activity icon is required.',
            'photo.image' => 'The activity background image must be with an extension of .jpg, .jpeg or .png.',
            'icon.mimetypes' => 'The activity icon must be a .svg file.',
            'color.required' => 'The activity background overlay color is required.',
        ];
        $validator = Validator::make($request->all(), [
            "default-name" => "required|unique:activities,name,".$activityItem->id,
            "color" => "required",
            "photo" => "image|mimes:jpeg,png",
            "icon" => "mimetypes:text/plain,image/png,image/svg",
        ], $messages);
        if (empty($activityItem->photo)) {
            $validator = Validator::make($request->all(), [
                "photo" => "required|image|mimes:jpeg,png",
            ]);
        }
        if (empty($activityItem->icon)) {
            $validator = Validator::make($request->all(), [
                "icon" => "required|mimetypes:text/plain,image/png,image/svg",
            ]);
        }
        if ($validator->fails()) {
            return redirect('/activity/' . $id . '/edit')
                ->withErrors($validator)
                ->withInput();
        }
        $data = $request->all();
        $activity = $this->mapDataToActivityEntity($data);
        $activity->id = $id;

        if (isset($data['photo'])) {
            $activity->photo = $request->file("photo")->store(UploadPaths::ACTIVITY_IMAGES_PATH . "/{$id}");
        }
        if (isset($data['icon']))
        {
            $iconMimeType = $request->file("icon")->getMimeType();
            if ($iconMimeType == "text/plain" || $iconMimeType == "image/svg"){
                $icon = md5($data['icon']->getClientOriginalName().time());
                $activity->icon = $request->file("icon")->storeAs(UploadPaths::ACTIVITY_ICONS_PATH . "/{$id}", $icon.'.svg');
            }
            else{
                $activity->icon = $request->file("icon")->store(UploadPaths::ACTIVITY_ICONS_PATH . "/{$id}");
            }
        }
        try {
            $this->activityService->updateActivity($activity);
        } catch (InvalidArgumentException $invalidArgumentException) {
        }
        /**
         * @var ActivityTranslation $activityTranslation
         */

        if (!empty($activity->activityTranslations)){
            foreach ($activity->activityTranslations as $activityTranslation) {

                $criteria=[
                    "activity-id" => $id,
                    "language-code" => $activityTranslation->code
                ];

                $activityTranslationItem = $this->activityTranslationService->getActivityTranslationWithCriteria($criteria);
                if ($activityTranslationItem) {
                    $activityTranslationItemId = $activityTranslationItem[0]->id;
                    if ($activityTranslation->name) {
                        $activityTranslation->id = $activityTranslationItemId;
                        try{
                            $this->activityTranslationService->updateActivityTranslation($activityTranslation);
                        }
                        catch (InvalidArgumentException $invalidArgumentException){
                        }
                    } else {
                        $this->activityTranslationService->deleteActivityTranslation($activityTranslationItemId);
                    }
                } else {
                    $activityTranslation->activity = new Activity();
                    $activityTranslation->activity->id = $activity->id;
                    try{
                        $this->activityTranslationService->addNewActivityTranslation($activityTranslation);
                    }
                    catch (InvalidArgumentException $invalidArgumentException){
                        dd("err");
                    }
                }
            }
        }
        return redirect("activity/list");
    }

    /**
     * create new activity
     * @param ActivityPostRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function createNewActivityAction(ActivityPostRequest $request)
    {
        $validated = $request->validated();
        $data = $request->all();

        $activity = $this->mapDataToActivityEntity($data);
        $lastActivityOrder = $this->activityService->getLastActivityOrder();
        if ($lastActivityOrder)
        {
            $activity->order = $lastActivityOrder->order + 1;
        } else {
            $activity->order = 1;
        }
        try {
            $createdActivity = $this->activityService->addNewActivity($activity);
        } catch (InvalidArgumentException $argumentException) {
        }
        $extension = $request->file("icon")->extension();
        $iconMimeType = $request->file("icon")->getMimeType();
        if ($iconMimeType == "text/plain" || $iconMimeType == "image/svg"){
            $icon = md5($data['icon']->getClientOriginalName().time());
            $iconPath = $request->file("icon")->storeAs(UploadPaths::ACTIVITY_ICONS_PATH . "/{$createdActivity->id}", $icon.'.svg');
        }
        else{
           $iconPath = $request->file("icon")->store(UploadPaths::ACTIVITY_ICONS_PATH . "/{$createdActivity->id}");
        }
        $photoPath = $request->file("photo")->store(UploadPaths::ACTIVITY_IMAGES_PATH . "/{$createdActivity->id}");
        $activityWithPath = new Activity();
        $activityWithPath->id = $createdActivity->id;
        $activityWithPath->photo = $photoPath;
        $activityWithPath->icon = $iconPath;

        try{
            $this->activityService->updateActivity($activityWithPath);
        } catch (InvalidArgumentException $invalidArgumentException) {

        }
        /**
         * @var ActivityTranslation $activityTranslation
         */
        if (!empty($activity->activityTranslations)){
            foreach ($activity->activityTranslations as $activityTranslation) {
                $activityTranslation->activity = $createdActivity;
                try{
                    $this->activityTranslationService->addNewActivityTranslation($activityTranslation);
                }
                catch (InvalidArgumentException $invalidArgumentException){
                    continue;
                }
            }
        }
        return redirect("activity/list");
    }

    /**
     * get all saved activities in the database
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listActivities(Request $request)
    {
        $data = $request->all();
        $criteria = [];

        $page         = $request->has('page')? abs((int) $request->get('page')) : 1;
        $count        = $request->has('limit')? abs((int) $request->get('limit')) : $this->count;
        $offset       = ($page - 1) * $count;

        if (isset($data["name-search"])) {
            $criteria["name-filter"] = $data["name-search"];
        }
        $activities = $this->activityService->getAllActivitiesListing($criteria);
//        $paging = $this->getSearchActivityPaging($criteria, $count, $offset);

        $data = [
            "entities" => $activities,
//            "paging" => $paging,
            "searchInput" => isset($data['name-search']) ? $data['name-search'] : ''
        ];
        return view("front.activity.list", $data);
    }

    /**
     * delete activity action
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteActivityAction($id)
    {
        try {
            $this->activityService->deleteActivity($id);
        } catch (QueryException $queryException) {
            switch ($queryException->getCode()) {
                case 23000:
                    $message = "Activity can not be deleted as it is being used by another instance in the system";
                    return redirect("activity/list")->with("delete-activity-error", $message);
                    break;
            }
        }
        return redirect("activity/list");
    }

    /**
     * display activity edit form
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function displayActivityEditForm($id)
    {
        try{
            $activityItem = $this->activityService->getActivityWithId($id);
        }catch (NotFoundException $otFoundException ){
            return redirect("activity/list");
        }
        $criteria['activity-id'] = $id;
        $activityItemTranslations = $this->activityTranslationService->getActivityTranslationWithCriteria($criteria);

        $availableLanguages = $this->systemAvailableLanguageService->getAllAvailableLanguages();
        $allAvailableLanguages = [];
        foreach ($availableLanguages as $availableLanguage) {
            $allAvailableLanguages[$availableLanguage->systemLanguage->code] = $availableLanguage->systemLanguage->language;
        }

        $name = [];
        foreach ($activityItemTranslations as $activityTranslation)
        {
            $languageCriteria['code'] = $activityTranslation->code;
            $activityLanguageName = $this->systemAvailableLanguageService->getLanguageWithCriteria($languageCriteria);
            $name[] = [
               "code" => $activityTranslation->code,
               "value" => $activityTranslation->name,
               "language" => $activityLanguageName[0]->systemLanguage->language
            ];
        }

        $actionUrl = "/activity/$id/update";

        $photoName = substr($activityItem->photo, strrpos($activityItem->photo, $id.'/'));
        $iconName = substr($activityItem->icon, strrpos($activityItem->icon, $id.'/'));

        $data = [
            "photo" => $activityItem->photo,
            "defaultName" => $activityItem->name,
            "photoName" => $photoName,
            "iconName" => $iconName,
            "activityTranslation" => $name ? $name : [],
            "icon" => $activityItem->icon,
            "color" => $activityItem->color,
            "actionUrl" => $actionUrl,
            "isFeatured" => $activityItem->isFeatured,
            "availableLanguages" => $allAvailableLanguages,
        ];
        return view("front.activity.activity-form", $data);
    }

    /**
     * order activities in list
     * @param $model
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function reorderActivities($model, Request $request){
        if ($request->ajax())
        {
            if( $model == 'activities' )
            {
                $list = $request->input('list');
                $arr = [];
                $i = 1;
                foreach ($list as $key => $value)
                {
                    $activity = ActivityModel::findOrFail((int) $value);
                    $activity->order = $i;
                    $activity->save();
                    array_push($arr, $activity);
                    $i++;
                }
                $data = [
                    'success' => true
                ];
                return response($data, 200)->header('Content-Type', 'text/plain');
            }
        }
    }

    /**
     * Map request data to @see Activity instance
     * @param array $data
     * @return Activity
     */
    private function mapDataToActivityEntity(array $data) : Activity
    {
        $activity = new Activity();

        if (isset($data["default-name"])) {
            $activity->name = $data["default-name"];
        }
        if (isset($data["activity-translation"]) && !empty($data["activity-translation"])) {
            foreach ($data["activity-translation"] as $langCode => $nameTranslation) {
                $activityTranslation = new ActivityTranslation();
                $activityTranslation->name = $nameTranslation;
                $activityTranslation->code = $langCode;
                $activity->activityTranslations[] = $activityTranslation;
            }
        }
        if (isset($data["icon"])) {
            $activity->icon = $data["icon"];
        }
        if (isset($data["photo"])) {
            $activity->photo = $data["photo"];
        }
        if (isset($data["color"])) {
            $activity->color = $data["color"];
        }
        if (isset($data["isFeatured"])) {
            $activity->isFeatured = $data["isFeatured"];
        } else {
            $activity->isFeatured = 0;
        }
        return $activity;
    }

    /**
     * activity search pagination
     * @param array $criteria
     * @param int|null $count
     * @param int|null $offset
     * @return \stdClass
     */
    private function getSearchActivityPaging(array $criteria, int $count = null, int $offset = null)
    {
        $paging = parent::initializePaging($count, $offset);
        $activities = $this->activityService->getActivityWithCriteria($criteria, 1, $offset + $paging->count);
        if (empty($activities)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }
}
