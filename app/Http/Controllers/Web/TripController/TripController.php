<?php

namespace App\Http\Controllers\Web\TripController;

use App\Http\Controllers\Abstracts\AbstractWebController;
use App\Http\Requests\TripRequest\TripRequest;
use App\Utility\UploadPaths;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Application\Exceptions\NotFoundException;
use Dev\Domain\Entity\Activity;
use Dev\Domain\Entity\City;
use Dev\Domain\Entity\Currency;
use Dev\Domain\Entity\Tag;
use Dev\Domain\Entity\TourGuideLanguage;
use Dev\Domain\Entity\Trip;
use Dev\Domain\Entity\TripAdditionalField;
use Dev\Domain\Entity\TripEvent;
use Dev\Domain\Entity\TripEventPrice;
use Dev\Domain\Entity\TripEventTourGuideLanguage;
use Dev\Domain\Entity\TripGalleryImage;
use Dev\Domain\Entity\TripPrice;
use Dev\Domain\Entity\TripTag;
use Dev\Domain\Entity\TripTourGuideLanguage;
use Dev\Domain\Entity\TripTranslation;
use Dev\Domain\Service\ActivityService\ActivityService;
use Dev\Domain\Service\CityService\CityService;
use Dev\Domain\Service\CurrencyService\CurrencyService;
use Dev\Domain\Service\SupplierTeamMemberService\SupplierTeamMemberService;
use Dev\Domain\Service\SupplierTeamMemberService\TripSupplierMemberService;
use Dev\Domain\Service\SystemLanguageService\SystemAvailableLanguageService;
use Dev\Domain\Service\TagService\TagService;
use Dev\Domain\Service\TourGuideLanguageService\TourGuideLanguageService;
use Dev\Domain\Service\TripService\TripAdditionalFieldService;
use Dev\Domain\Service\TripService\TripEventPriceService;
use Dev\Domain\Service\TripService\TripEventService;
use Dev\Domain\Service\TripService\TripEventTourGuideLanguageService;
use Dev\Domain\Service\TripService\TripGalleryImageService;
use Dev\Domain\Service\TripService\TripPriceService;
use Dev\Domain\Service\TripService\TripService;
use Dev\Domain\Service\TripService\TripTagService;
use Dev\Domain\Service\TripService\TripTourGuideLanguageService;
use Dev\Domain\Service\TripService\TripTranslationService;
use Dev\Domain\Service\UserService\UserService;
use Dev\Domain\Utility\ConfirmationType;
use Dev\Domain\Utility\DateTimeFormat;
use Dev\Domain\Utility\PreDefinedPeriods;
use Dev\Domain\Utility\TripPriceAgeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Validator;

/**
 * TripController Class responsible for all actions related to trip
 * @package App\Http\Controllers\Web\TripController
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class TripController extends AbstractWebController
{
    /**
     * @var TripService $tripService instance from TripService
     */
    private $tripService;

    /**
     * @var int page count
     */
    private $count = 30;

    /**
     * @var CityService $cityService instance from CityService
     */
    private $cityService;

    /**
     * @var ActivityService $activityService instance from ActivityService
     */
    private $activityService;

    /**
     * @var TripTourGuideLanguageService $TripTourGuideLanguageService instance from TripTourGuideLanguageService
     */
    private $tripTourGuideLanguageService;

    /**
     * @var TourGuideLanguageService $tourGuideLanguageService instance from TourGuideLanguageService
     */
    private $tourGuideLanguageService;

    /**
     * @var CurrencyService $currencyService instance from CurrencyService
     */
    private $currencyService;

    /**
     * @var TripPriceService $tripPriceService instance from TripPriceService
     */
    private $tripPriceService;

    /**
     * @var TripGalleryImageService $tripGalleryImageService instance from TripGalleryImageService
     */
    private $tripGalleryImageService;

    /**
     * @var TripEventService $tripEventService instance from TripEventService
     */
    private $tripEventService;

    /**
     * @var TripEventPriceService $tripEventPriceService instance from TripEventPriceService
     */
    private $tripEventPriceService;

    /**
     * @var TripEventTourGuideLanguageService $tripEventTourGuideLanguageService instance from TripEventTourGuideLanguageService
     */
    private $tripEventTourGuideService;

    /**
     * @var TripAdditionalFieldService $tripAdditionalFieldService instance from TripAdditionalFieldService
     */
    private $tripAdditionalFieldService;

    /**
     * @var SupplierTeamMemberService $supplierTeamMemberService instance from SupplierTeamMemberService
     */
    private $supplierTeamMemberService;

    /**
     * @var TripSupplierMemberService $tripSupplierMemberService instance from TripSupplierMemberService
     */
    private $tripSupplierMemberService;

    /**
     * @var SystemAvailableLanguageService $systemAvailableLanguageService instance from SystemAvailableLanguageService
     */
    private $systemAvailableLanguageService;

    /**
     * @var TripTranslationService $tripTranslationService instance from TripTranslationService
     */
    private $tripTranslationService;

    /**
     * @var TagService $tagService instance from TagService
     */
    private $tagService;

    /**
     * @var TripTagService $tripTagService instance from TripTagService
     */
    private $tripTagService;

    /**
     * @var UserService $userService instance from UserService
     */
    private $userService;

    /**
     * TripController constructor.
     * @param Request $request instance from Request
     * @param TripService $tripService instance from TripService
     * @param CityService $cityService instance from CityService
     * @param ActivityService $activityService instance from ActivityService
     * @param TourGuideLanguageService $tourGuideLanguageService instance from TourGuideLanguageService
     * @param TripTourGuideLanguageService $TripTourGuideLanguageService instance from TripTourGuideLanguageService
     * @param CurrencyService $currencyService instance from CurrencyService
     * @param TripPriceService $tripPriceService instance from TripPriceService
     * @param TripGalleryImageService $tripGalleryImageService instance from TripGalleryImageService
     * @param TripEventService $tripEventService instance from TripEventService
     * @param TripEventPriceService $tripEventPriceService instance from TripEventPriceService
     * @param TripEventTourGuideLanguageService $tripEventTourGuideLanguageService instance from TripEventTourGuideLanguageService
     * @param TripAdditionalFieldService $tripAdditionalFieldService instance from TripAdditionalFieldService
     * @param SupplierTeamMemberService $supplierTeamMemberService instance from SupplierTeamMemberService
     * @param TripSupplierMemberService $tripSupplierMemberService instance from TripSupplierMemberService
     * @param SystemAvailableLanguageService $systemAvailableLanguageService instance from SystemAvailableLanguageService
     * @param TripTranslationService $tripTranslationService instance from TripTranslationService
     * @param TagService $tagService instance from TagService
     * @param TripTagService $tripTagService instance from TripTagService
     * @param UserService $userService instance from UserService
     */
    public function __construct(
        Request $request,
        TripService $tripService,
        CityService $cityService,
        ActivityService $activityService,
        TourGuideLanguageService $tourGuideLanguageService,
        TripTourGuideLanguageService $TripTourGuideLanguageService,
        CurrencyService $currencyService,
        TripPriceService $tripPriceService,
        TripGalleryImageService $tripGalleryImageService,
        TripEventService $tripEventService,
        TripEventPriceService $tripEventPriceService,
        TripEventTourGuideLanguageService $tripEventTourGuideLanguageService,
        TripAdditionalFieldService $tripAdditionalFieldService,
        SupplierTeamMemberService $supplierTeamMemberService,
        TripSupplierMemberService $tripSupplierMemberService,
        SystemAvailableLanguageService $systemAvailableLanguageService,
        TripTranslationService $tripTranslationService,
        TagService $tagService,
        TripTagService $tripTagService,
        UserService $userService
    ) {
        parent::__construct($request);
        $this->tripService = $tripService;
        $this->cityService = $cityService;
        $this->activityService = $activityService;
        $this->tripTourGuideLanguageService = $TripTourGuideLanguageService;
        $this->currencyService = $currencyService;
        $this->tripPriceService = $tripPriceService;
        $this->tripGalleryImageService = $tripGalleryImageService;
        $this->tripEventService = $tripEventService;
        $this->tripEventPriceService = $tripEventPriceService;
        $this->tripEventTourGuideService = $tripEventTourGuideLanguageService;
        $this->tourGuideLanguageService = $tourGuideLanguageService;
        $this->tripAdditionalFieldService = $tripAdditionalFieldService;
        $this->supplierTeamMemberService = $supplierTeamMemberService;
        $this->tripSupplierMemberService = $tripSupplierMemberService;
        $this->systemAvailableLanguageService = $systemAvailableLanguageService;
        $this->tripTranslationService = $tripTranslationService;
        $this->tagService = $tagService;
        $this->tripTagService = $tripTagService;
        $this->userService = $userService;
    }

    /**
     * Display trip creation form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayTripCreationForm(Request $request)
    {
        $supplier = $request->session()->get("webAuthUser");
        $cities = $this->getAllLocations();
        $activities = $this->getAllActivities();
        $tourGuideLanguages = $this->getAllTourGuideLanguages();
        $currencies = $this->getAllCurrencies();
        $pricePeriods = PreDefinedPeriods::getPricePeriods();
        $confirmationTypePeriods = PreDefinedPeriods::getConfirmationTypePeriod();
        $tags = $this->tagService->getAllTags();
        $allTags = [];
        foreach ($tags as $tag) {
            $allTags[$tag->id] = $tag->name;
        }
        $availableLanguages = $this->systemAvailableLanguageService->getAllAvailableLanguages();
        $allAvailableLanguages = [];
        foreach ($availableLanguages as $availableLanguage) {
            $allAvailableLanguages[$availableLanguage->systemLanguage->code] = $availableLanguage->systemLanguage->language;
        }
        $data = [
            "cities" => $cities,
            "activities" => $activities,
            "tourGuideLanguages" => $tourGuideLanguages,
            "currencies" => $currencies,
            "pricePeriods" => $pricePeriods,
            "confirmationTypePeriods" => $confirmationTypePeriods,
            "tags" => $allTags,
            "availableLanguages" => $allAvailableLanguages,
        ];
        return view("front.trip.trip-form", $data);
    }

    /**
     * @param TripRequest $request
     */
    public function createNewTripAction(TripRequest $request)
    {
        $validator = $request->validated();
        $user = $request->session()->get("webAuthUser");
        $data = $request->all();
        $trip = $this->mapDataToTripEntity($data);
        $tripChildPrice = $this->mapDataToTripChildPrice($data);
        $tripInfantPrice = $this->mapDataToTripInfantPrice($data);
        $trip->createdBy = $user;
        try {
            $createdTrip = $this->tripService->createTrip($trip);
        } catch (InvalidArgumentException $argumentException) {
            return redirect('trip/create')
                ->withErrors($validator)
                ->withInput();
        }
        $photoPath = $request->file("photo")->store(UploadPaths::TRIP_IMAGES_PATH . "/{$createdTrip->id}");
        if (isset($data["crop-image-temp-path"])) {
            $tempFilePath = $data["crop-image-temp-path"];
            $tempFileName = basename($tempFilePath);
            $fileExtension = $request->file("photo")->getClientOriginalExtension();
            Storage::move(
                $tempFilePath,
                str_replace(
                    "{trip-id}",
                    $createdTrip->id,
                    UploadPaths::TRIP_IMAGES_CROP_PATH
                ) . '/' . $tempFileName . $fileExtension
            );
        }
        $tripWithPhotoPath = new Trip();
        $tripWithPhotoPath->id = $createdTrip->id;
        $tripWithPhotoPath->photo = $photoPath;
        $trip->photo = $photoPath;
        $this->tripService->updateTripMainPhoto($tripWithPhotoPath);
        foreach ($trip->tripDataMultiLanguages as $tripTranslation)
            /**
             * @var TripTranslation $tripTranslation
             */{
            $tripTranslation->trip = $createdTrip;
            try {
                $this->tripTranslationService->createTripTranslation($tripTranslation);
            } catch (InvalidArgumentException $invalidArgumentException) {

            }
        }
        if (isset($trip->tourGuideLanguages)) {
            /**
             * @var TripTourGuideLanguage $tripTourGuideLanguage
             */
            foreach ($trip->tourGuideLanguages as $tripTourGuideLanguage) {
                $tripTourGuideLanguage->trip = $createdTrip;
                try {
                    $this->tripTourGuideLanguageService->addNewTripTourGuideLanguage($tripTourGuideLanguage);
                } catch (InvalidArgumentException $invalidArgumentException) {
                    continue;
                }
            }
        }
        if ($gallery = $request->file("gallery")) {
            $tripGalleryImage = new TripGalleryImage();
            $tripGalleryImage->trip = $createdTrip;
            foreach ($gallery as $image) {
                $photoPath = $image->store(UploadPaths::TRIP_IMAGES_PATH . "/{$createdTrip->id}");
                $tripGalleryImage->photo = $photoPath;
                try {
                    $this->tripGalleryImageService->addNewTripGalleryImage($tripGalleryImage);
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            }
        }
        if ($tripChildPrice->price) {
            $tripChildPrice->trip = $createdTrip;
            try {
                $this->tripPriceService->addNewTripPrice($tripChildPrice);
            } catch (InvalidArgumentException $invalidArgumentException) {

            }
        }
        if ($tripInfantPrice->price) {
            $tripInfantPrice->trip = $createdTrip;
            try {
                $this->tripPriceService->addNewTripPrice($tripInfantPrice);
            } catch (InvalidArgumentException $invalidArgumentException) {

            }
        }
        if ($trip->additionalFields) {
            foreach ($trip->additionalFields as $tripAdditionalField) {
                $tripAdditionalField->trip = $createdTrip;
                try {
                    $this->tripAdditionalFieldService->addNewTripAdditionalField($tripAdditionalField);
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            }
        }
        $tripEvents = $this->mapDataToTripEvents($data);
        foreach ($tripEvents as $tripEvent) {
            /**
             * @var TripEvent $tripEvent
             */
            $tripEvent->trip = $createdTrip;
            try {
                $createdTripEvent = $this->tripEventService->createTripEvent($tripEvent);
            } catch (InvalidArgumentException $invalidArgumentException) {
                continue;
            }
            if ($tripEvent->childPrice) {
                $tripEvent->childPrice->tripEvent = $createdTripEvent;
                try {
                    $this->tripEventPriceService->addNewTripEventPrice($tripEvent->childPrice);
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            }
            if ($tripEvent->infantPrice) {
                $tripEvent->infantPrice->tripEvent = $createdTripEvent;
                try {
                    $this->tripEventPriceService->addNewTripEventPrice($tripEvent->infantPrice);
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            }
            if ($tripEvent->tourGuideLanguages) {
                /**
                 * @var TripEventTourGuideLanguage $tripEventtourGuideLanguage
                 */
                foreach ($tripEvent->tourGuideLanguages as $tripEventTourGuideLanguage) {
                    $tripEventTourGuideLanguage->tripEvent = $createdTripEvent;
                    try {
                        $this->tripEventTourGuideService->addNewTripEventTourGuideLanguage($tripEventTourGuideLanguage);
                    } catch (InvalidArgumentException $invalidArgumentException) {

                    }
                }
            }
        }
        if (isset($trip->tags)) {
            /**
             * @var TripTag $tripTag
             */
            foreach ($trip->tags as $tripTag) {
                $tripTag->trip = $createdTrip;
                try {
                    $this->tripTagService->addNewTripTag($tripTag);
                } catch (InvalidArgumentException $invalidArgumentException) {
                    continue;
                }
            }
        }
        return redirect("trip/list");
    }

    /**
     * List all trips
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listTrips(Request $request)
    {
        $loggedInUser = $request->session()->get("webAuthUser");
        $requestData = $request->all();
        $createdBy = $request->get("created-by");
        $criteria = [];
        $page   = $request->has('page')? abs((int) $request->get('page')) : 1;
        $count  = $request->has('limit')? abs((int) $request->get('limit')) : $this->count;
        $offset = ($page - 1) * $count;
        $searchInput = isset($requestData['name-search']) ? $requestData['name-search'] : '';
        $activityInput = isset($requestData['activity-id']) ? $requestData['activity-id'] : '';
        $cityInput = isset($requestData['city-id']) ? $requestData['city-id'] : '';
        $supplierInput = isset($requestData['supplier-id']) ? $requestData['supplier-id'] : '';
        $criteria["customer-id"] = $loggedInUser->relatedCustomer->id;
        if (isset($requestData["name-search"])) {
            $criteria["name-filter"] = $requestData["name-search"];
        }
        if (isset($requestData["city-id"])) {
            $criteria["city-ids"][] = $requestData["city-id"];
        }
        if (isset($requestData["activity-id"])) {
            $criteria["activity-ids"][] = $requestData["activity-id"];
        }
        if (isset($createdBy)) {
            $criteria["created-by"] = $createdBy;
        } elseif ($supplierInput) {
            $criteria["created-by"] = $supplierInput;
        }
        $cities = $this->getAllLocations();
        $activities = $this->getAllActivities();
        $suppliersCriteria = [
            "role-id" => 3,
            "customer-id" => $loggedInUser->relatedCustomer->id
        ];
        $suppliers = $this->userService->getUserWithCriteria($suppliersCriteria);
        $trips = $this->tripService->getTripsWithCriteria($criteria, $count, $offset);

        $paging = $this->tripService->getPaginationLinks($criteria, $count, $offset);
        $data = [];
        foreach ($trips as $trip) {
            $tripData = [
                "id" => $trip->id,
                "name" => $trip->name,
                "photo" => $trip->photoUrl,
            ];
            $data["entities"][] = $tripData;
        }
        $data['searchInput'] = $searchInput;
        $data['activityInput'] = $activityInput;
        $data['cityInput'] = $cityInput;
        $data['supplierInput'] = $supplierInput;
        $data['paging'] = $paging;
        $data['activities'] = $activities;
        $data['cities'] = $cities;
        $data['suppliers'] = $suppliers;
        $data['pageName'] = "Trip listing";
        return view("front.trip.list", $data);
    }

    /**
     * Delete trip item
     * @param int $id trip id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deleteTrip(int $id)
    {
        $this->tripService->deleteTrip($id);
        return redirect("trip/list");
    }

    /**
     * Display trip modification form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayTripModificationForm(Request $request, int $id)
    {
        try {
            $existedTrip = $this->tripService->getTripWithId($id);
        } catch (NotFoundException $notFoundException) {
            return redirect("trip/list");
        }
        $cities = $this->getAllLocations();
        $activities = $this->getAllActivities();
        $tourGuideLanguages = $this->getAllTourGuideLanguages();
        $currencies = $this->getAllCurrencies();
        $pricePeriods = PreDefinedPeriods::getPricePeriods();
        $confirmationTypePeriods = PreDefinedPeriods::getConfirmationTypePeriod();
        $tags = $this->tagService->getAllTags();
        $allTags = [];
        $gallery = [];
        foreach ($tags as $tag) {
            $allTags[$tag->id] = $tag->name;
        }
        $availableLanguages = $this->systemAvailableLanguageService->getAllAvailableLanguages();
        $allAvailableLanguages = [];
        foreach ($availableLanguages as $availableLanguage) {
            $allAvailableLanguages[$availableLanguage->systemLanguage->code] = $availableLanguage->systemLanguage->language;
        }
        $tripTourGuideLanguages = [];
        if (isset($existedTrip->tourGuideLanguages)) {
            foreach ($existedTrip->tourGuideLanguages as $tourGuideLanguage) {
                $tripTourGuideLanguages[] = $tourGuideLanguage->tourGuideLanguage->id;
            }
        }
        if (isset($existedTrip->gallery)) {
            foreach ($existedTrip->gallery as $tripGalleryImage) {
                $gallery[$tripGalleryImage->id] = [
                    "photo" => $tripGalleryImage->photoUrl,
                    "name" => basename($tripGalleryImage->photo)
                ];
            }
        }
        $tripLanguages = [];
        if (isset($existedTrip->tripDataMultiLanguages)) {
            foreach ($existedTrip->tripDataMultiLanguages as $tripLanguage) {
                $tripLanguages[$tripLanguage->languageCode] = [
                    "translationId" => $tripLanguage->id,
                    "displayName" => ($tripLanguage->displayName) ? $tripLanguage->displayName : '',
                    "description" => ($tripLanguage->description) ? trim($tripLanguage->description) : '',
                    "packageOption" => ($tripLanguage->packageOption) ? $tripLanguage->packageOption : '',
                    "inclusiveOf" => ($tripLanguage->inclusiveOf) ? $tripLanguage->inclusiveOf : '',
                    "notInclusiveOf" => ($tripLanguage->notInclusiveOf) ? $tripLanguage->notInclusiveOf : '',
                    "meetUpInfo" => ($tripLanguage->meetUpInfo) ? $tripLanguage->meetUpInfo : '',
                    "cancellationPolicy" => ($tripLanguage->cancellationPolicy) ? $tripLanguage->cancellationPolicy : '',
                ];
            }
        }
        if (isset($existedTrip->additionalFields)) {
            foreach ($existedTrip->additionalFields as $additionalField) {
                /**
                 * @var TripAdditionalField $additionalField
                 */
                $tripLanguages[$additionalField->languageCode]["additionalField"] = [
                    "id" => $additionalField->id,
                    "title" => isset($additionalField->title) ?
                        $additionalField->title : '',
                    "description" => isset($additionalField->description) ?
                        $additionalField->description : ''
                ];
            }
        }
        $events = [];
        if (isset($existedTrip->events)) {
            foreach ($existedTrip->events as $event) {
                $tripEventTourGuideLanguages = [];
                if (isset($event->tourGuideLanguages)) {
                    foreach ($event->tourGuideLanguages as $tourGuideLanguage) {
                        $tripEventTourGuideLanguages[] = $tourGuideLanguage->tourGuideLanguage->id;
                    }
                }
                $events[$event->id] = [
                    "startDate" => isset($event->startDate) ?
                        date(DateTimeFormat::TRIP_FORM_DATE_FORMAT, strtotime($event->startDate)) : '',
                    "startTime" => isset($event->startTime) ?
                        date(DateTimeFormat::TRIP_FORM_TIME_FORMAT, strtotime($event->startTime)) : '',
                    "endDate" => isset($event->endDate) ?
                        date(DateTimeFormat::TRIP_FORM_DATE_FORMAT, strtotime($event->endDate)) : '',
                    "endTime" => isset($event->endTime) ?
                        date(DateTimeFormat::TRIP_FORM_TIME_FORMAT, strtotime($event->endTime)) : '',
                    "price" => $event->price ? $event->price : '',
                    "currency" => isset($event->currency->id) ?
                        $event->currency->id : '',
                    "childPrice" => [
                        "id" => isset($event->childPrice->id) ? $event->childPrice->id : NULL,
                        "type" => isset($event->childPrice->type) ? TripPriceAgeType::getEnumValue($event->childPrice->type) : '',
                        "price" => isset($event->childPrice->price) ? $event->childPrice->price : '',
                        "ageFrom" => isset($event->childPrice->ageFrom) ? $event->childPrice->ageFrom : '',
                        "ageFromPeriod" => isset($event->childPrice->ageFromPeriod) ?
                            PreDefinedPeriods::getEnumValue($event->childPrice->ageFromPeriod) : '',
                        "ageTo" => isset($event->childPrice->ageTo) ? $event->childPrice->ageTo : '',
                        "ageToPeriod" => isset($event->childPrice->ageToPeriod) ?
                            PreDefinedPeriods::getEnumValue($event->childPrice->ageToPeriod) : ''
                    ],
                    "infantPrice" => [
                        "id" => isset($event->infantPrice->id) ? $event->infantPrice->id : NULL,
                        "type" => isset($event->infantPrice->type) ? TripPriceAgeType::getEnumValue($event->infantPrice->type) : '',
                        "price" => isset($event->infantPrice->price) ? $event->infantPrice->price : '',
                        "ageFrom" => isset($event->infantPrice->ageFrom) ? $event->infantPrice->ageFrom : '',
                        "ageFromPeriod" => isset($event->infantPrice->ageFromPeriod) ?
                            PreDefinedPeriods::getEnumValue($event->infantPrice->ageFromPeriod) : '',
                        "ageTo" => isset($event->infantPrice->ageTo) ? $event->infantPrice->ageTo : '',
                        "ageToPeriod" => isset($event->infantPrice->ageToPeriod) ?
                            PreDefinedPeriods::getEnumValue($event->infantPrice->ageToPeriod) : ''
                    ],
                    "tourGuideLanguages" => $tripEventTourGuideLanguages,
                ];
                unset($tripEventTourGuideLanguages);
            }
        }
        $tripTags = [];
        if (isset($existedTrip->tags)) {
            foreach ($existedTrip->tags as $tripTag) {
                $tripTags[] = $tripTag->tag->id;
            }
        }
        $data = [
            "cities" => $cities,
            "activities" => $activities,
            "tourGuideLanguages" => $tourGuideLanguages,
            "currencies" => $currencies,
            "pricePeriods" => $pricePeriods,
            "confirmationTypePeriods" => $confirmationTypePeriods,
            "tags" => $allTags,
            "availableLanguages" => $allAvailableLanguages,
            "trip" => [
                "id" => $existedTrip->id,
                "name" => $existedTrip->name ? $existedTrip->name : '',
                "destination" => isset($existedTrip->location->id) ? $existedTrip->location->id : NULL,
                "currency" => isset($existedTrip->currency->id) ?
                    $existedTrip->currency->id : '',
                "price" => $existedTrip->price ? $existedTrip->price : '',
                "photoUrl" => $existedTrip->photoUrl,
                "childPrice" => [
                    "id" => isset($existedTrip->childPrice->id) ? $existedTrip->childPrice->id : NULL,
                    "type" => isset($existedTrip->childPrice->type) ? TripPriceAgeType::getEnumValue($existedTrip->childPrice->type) : '',
                    "price" => isset($existedTrip->childPrice->price) ? $existedTrip->childPrice->price : '',
                    "ageFrom" => isset($existedTrip->childPrice->ageFrom) ? $existedTrip->childPrice->ageFrom : '',
                    "ageFromPeriod" => isset($existedTrip->childPrice->ageFromPeriod) ?
                        PreDefinedPeriods::getEnumValue($existedTrip->childPrice->ageFromPeriod) : '',
                    "ageTo" => isset($existedTrip->childPrice->ageTo) ? $existedTrip->childPrice->ageTo : '',
                    "ageToPeriod" => isset($existedTrip->childPrice->ageToPeriod) ?
                        PreDefinedPeriods::getEnumValue($existedTrip->childPrice->ageToPeriod) : ''
                ],
                "infantPrice" => [
                    "id" => isset($existedTrip->infantPrice->id) ? $existedTrip->infantPrice->id : NULL,
                    "type" => isset($existedTrip->infantPrice->type) ? TripPriceAgeType::getEnumValue($existedTrip->infantPrice->type) : '',
                    "price" => isset($existedTrip->infantPrice->price) ? $existedTrip->infantPrice->price : '',
                    "ageFrom" => isset($existedTrip->infantPrice->ageFrom) ? $existedTrip->infantPrice->ageFrom : '',
                    "ageFromPeriod" => isset($existedTrip->infantPrice->ageFromPeriod) ?
                        PreDefinedPeriods::getEnumValue($existedTrip->infantPrice->ageFromPeriod) : '',
                    "ageTo" => isset($existedTrip->infantPrice->ageTo) ? $existedTrip->infantPrice->ageTo : '',
                    "ageToPeriod" => isset($existedTrip->infantPrice->ageToPeriod) ?
                        PreDefinedPeriods::getEnumValue($existedTrip->infantPrice->ageToPeriod) : ''
                ],
                "activity" => isset($existedTrip->activity->id) ?
                    $existedTrip->activity->id : '',
                "confirmationType" => isset($existedTrip->confirmationType) ?
                    ConfirmationType::getEnumValue($existedTrip->confirmationType) : '',
                "timeToConfirm" => isset($existedTrip->timeToConfirm) ? $existedTrip->timeToConfirm : '',
                "timeToConfirmType" => isset($existedTrip->timeToConfirmType) ?
                    PreDefinedPeriods::getEnumValue($existedTrip->timeToConfirmType) : '',
                "startDate" => isset($existedTrip->startDate) ?
                    date(DateTimeFormat::TRIP_FORM_DATE_FORMAT, strtotime($existedTrip->startDate)) : '',
                "startTime" => isset($existedTrip->startTime) ?
                    date(DateTimeFormat::TRIP_FORM_TIME_FORMAT, strtotime($existedTrip->startTime)) : '',
                "endDate" => isset($existedTrip->endDate) ?
                    date(DateTimeFormat::TRIP_FORM_DATE_FORMAT, strtotime($existedTrip->endDate)) : '',
                "endTime" => isset($existedTrip->endTime) ?
                    date(DateTimeFormat::TRIP_FORM_TIME_FORMAT, strtotime($existedTrip->endTime)) : '',
                "tourGuideLanguages" => $tripTourGuideLanguages,
                "tripDataMultiLanguages" => $tripLanguages,
                "events" => $events,
                "tags" => $tripTags,
                "gallery" => $gallery
            ]
        ];
        return view("front.trip.trip-edit-form", $data);
    }

    /**
     * @param TripRequest $request
     * @param int $id
     */
    public function updateTripAction(TripRequest $request, int $id)
    {
        try {
            $existedTrip = $this->tripService->getTripWithId($id);
        } catch (NotFoundException $notFoundException) {
            return redirect("trip/list");
        }
        $validator = $request->validated();
        $user = auth()->user();
        $data = $request->all();
        $trip = $this->mapDataToTripEntity($data);
        $tripChildPrice = $this->mapDataToTripChildPrice($data);
        $tripInfantPrice = $this->mapDataToTripInfantPrice($data);
        $trip->modifiedBy = $user;
        $trip->id = $id;
        try {
            $this->tripService->updateTrip($trip);
        } catch (InvalidArgumentException $argumentException) {
            return redirect("trip/{$id}/edit")
                ->withErrors($validator)
                ->withInput();
        }
        $tripTourGuideLanguages = [];
        if (isset($existedTrip->tourGuideLanguages)) {
            foreach ($existedTrip->tourGuideLanguages as $tourGuideLanguage) {
                $tripTourGuideLanguages[] = $tourGuideLanguage->tourGuideLanguage->id;
            }
        }
        if ($request->hasFile("photo")) {
            $photoPath = $request->file("photo")->store(UploadPaths::TRIP_IMAGES_PATH . "/{$id}");
            $tripWithPhotoPath = new Trip();
            $tripWithPhotoPath->id = $id;
            $tripWithPhotoPath->photo = $photoPath;
            $trip->photo = $photoPath;
            $this->tripService->updateTripMainPhoto($tripWithPhotoPath);
            Storage::delete($existedTrip->photo);
        }
        $existedTripTranslationsIds = [];
        foreach ($trip->tripDataMultiLanguages as $tripTranslation)
            /**
             * @var TripTranslation $tripTranslation
             */{
            $tripTranslation->trip = $trip;
            if ($tripTranslation->id) {
                $existedTripTranslationsIds[] = $tripTranslation->id;
                try {
                    $this->tripTranslationService->updateTripTranslation($tripTranslation);
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            } else {
                try {
                    $createdTripTranslation = $this->tripTranslationService->createTripTranslation($tripTranslation);
                    $existedTripTranslationsIds[] = $createdTripTranslation->id;
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            }
        }
        if ($existedTripTranslationsIds) {
            try {
                $this->tripTranslationService->deleteTranslationExcept($id, $existedTripTranslationsIds);
            } catch (InvalidArgumentException $exception) {

            }
        }
        if (isset($trip->tourGuideLanguages)) {
            $tripTourGuideLanguageToKeep = [];
            /**
             * @var TripTourGuideLanguage $tripTourGuideLanguage
             */
            foreach ($trip->tourGuideLanguages as $tripTourGuideLanguage) {
                $tripTourGuideLanguageToKeep[] = $tripTourGuideLanguage->tourGuideLanguage->id;
                if (in_array($tripTourGuideLanguage->tourGuideLanguage->id, $tripTourGuideLanguages)) {
                    continue;
                }
                $tripTourGuideLanguage->trip = $trip;
                try {
                    $this->tripTourGuideLanguageService->addNewTripTourGuideLanguage($tripTourGuideLanguage);
                } catch (InvalidArgumentException $invalidArgumentException) {
                    continue;
                }
            }
            if ($tripTourGuideLanguageToKeep) {
                try {
                    $this->tripTourGuideLanguageService->deleteTourGuideLanguagesExcept($id, $tripTourGuideLanguageToKeep);
                } catch (InvalidArgumentException $exception) {

                }
            }
        }
        if ($gallery = $request->file("gallery")) {
            $tripGalleryImage = new TripGalleryImage();
            $tripGalleryImage->trip = $trip;
            foreach ($gallery as $image) {
                $photoPath = $image->store(UploadPaths::TRIP_IMAGES_PATH . "/{$trip->id}");
                $tripGalleryImage->photo = $photoPath;
                try {
                    $this->tripGalleryImageService->addNewTripGalleryImage($tripGalleryImage);
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            }
        }
        if (!$tripChildPrice->id) {
            $tripChildPrice->trip = $trip;
            try {
                $this->tripPriceService->addNewTripPrice($tripChildPrice);
            } catch (InvalidArgumentException $invalidArgumentException) {

            }
        } else {
            $tripChildPrice->trip = $trip;
            try {
                $this->tripPriceService->updateTripPrice($tripChildPrice);
            } catch (InvalidArgumentException $invalidArgumentException) {

            }
        }
        if (!$tripInfantPrice->id) {
            $tripInfantPrice->trip = $trip;
            try {
                $this->tripPriceService->addNewTripPrice($tripInfantPrice);
            } catch (InvalidArgumentException $invalidArgumentException) {

            }
        } else {
            $tripInfantPrice->trip = $trip;
            try {
                $this->tripPriceService->updateTripPrice($tripInfantPrice);
            } catch (InvalidArgumentException $invalidArgumentException) {

            }
        }
        if ($trip->additionalFields) {
            $fieldsToKeep = [];
            foreach ($trip->additionalFields as $tripAdditionalField) {
                if ($tripAdditionalField->id) {
                    $tripAdditionalField->trip = $trip;
                    try {
                        $this->tripAdditionalFieldService->updateTripAdditionalField($tripAdditionalField);
                    } catch (InvalidArgumentException $invalidArgumentException) {

                    }
                } else {
                    $tripAdditionalField->trip = $trip;
                    try {
                        $this->tripAdditionalFieldService->addNewTripAdditionalField($tripAdditionalField);
                    } catch (InvalidArgumentException $invalidArgumentException) {

                    }
                }
            }
            if ($fieldsToKeep) {
                $this->tripAdditionalFieldService->deleteTripAdditionalFieldsExcept($id, $fieldsToKeep);
            }
        } else {
            $this->tripAdditionalFieldService->deleteAllTripAdditionalFields($id);
        }
        $tripEvents = $this->mapDataToTripEvents($data);

        /**
         * @var TripEvent $tripEvent
         */
        foreach ($tripEvents as $tripEvent) {
            $tripEvent->trip = $trip;
            if (isset($tripEvent->id)) {
                try {
                    $createdTripEvent = $this->tripEventService->updateTripEvent($tripEvent);
                } catch (InvalidArgumentException $invalidArgumentException) {
                    continue;
                }
            } else {
                try {
                    $createdTripEvent = $this->tripEventService->createTripEvent($tripEvent);
                } catch (InvalidArgumentException $invalidArgumentException) {
                    continue;
                }
            }
            if (isset($tripEvent->childPrice->id)) {
                try {
                    $this->tripEventPriceService->updateTripEventPrice($tripEvent->childPrice);
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            } elseif (isset($tripEvent->childPrice)) {
                $tripEvent->childPrice->tripEvent = $createdTripEvent;
                try {
                    $this->tripEventPriceService->addNewTripEventPrice($tripEvent->childPrice);
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            } else {
                $this->tripEventPriceService->deleteTripEventPriceWithType($createdTripEvent->id, TripPriceAgeType::CHILD_ENUM_VALUE);
            }
            if (isset($tripEvent->infantPrice->id)) {
                $tripEvent->infantPrice->tripEvent = $createdTripEvent;
                try {
                    $this->tripEventPriceService->updateTripEventPrice($tripEvent->infantPrice);
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            } elseif (isset($tripEvent->infantPrice)) {
                $tripEvent->infantPrice->tripEvent = $createdTripEvent;
                try {
                    $this->tripEventPriceService->addNewTripEventPrice($tripEvent->infantPrice);
                } catch (InvalidArgumentException $invalidArgumentException) {

                }
            } else {
                $this->tripEventPriceService->deleteTripEventPriceWithType($createdTripEvent->id, TripPriceAgeType::INFANT_ENUM_VALUE);
            }
            if (isset($tripEvent->tourGuideLanguages)) {
                $tourGuideLanguageToKeep = [];
                $eventTourGuideLanguageIds = $createdTripEvent->getTourGuideLanguageIds();
                /**
                 * @var TripEventTourGuideLanguage $tripEventTourGuideLanguage
                 */
                foreach ($tripEvent->tourGuideLanguages as $tripEventTourGuideLanguage) {
                    $tourGuideLanguageToKeep[] = $tripEventTourGuideLanguage->tourGuideLanguage->id;
                    if (in_array($tripEventTourGuideLanguage->tourGuideLanguage->id, $eventTourGuideLanguageIds)) {
                        continue;
                    }
                    $tripEventTourGuideLanguage->tripEvent = $createdTripEvent;
                    try {
                        $this->tripEventTourGuideService->addNewTripEventTourGuideLanguage($tripEventTourGuideLanguage);
                    } catch (InvalidArgumentException $invalidArgumentException) {

                    }
                }
                if ($tourGuideLanguageToKeep) {
                    try {
                        $this->tripEventTourGuideService->deleteTourGuideLanguagesExcept($createdTripEvent->id, $tourGuideLanguageToKeep);
                    } catch (InvalidArgumentException $exception) {

                    }
                }
            } else {
                $this->tripEventTourGuideService->deleteAllTripEventTourGuideLanguages($createdTripEvent->id);
            }
        }
        if (isset($trip->tags)) {
            $existedTripTags = $existedTrip->getTagsIds();
            $tagsToKeepIds = [];
            /**
             * @var TripTag $tripTag
             */
            foreach ($trip->tags as $tripTag) {
                $tagsToKeepIds[] = $tripTag->tag->id;
                if (in_array($tripTag->tag->id, $existedTripTags)) {
                    continue;
                }
                $tripTag->trip = $trip;
                try {
                    $this->tripTagService->addNewTripTag($tripTag);
                } catch (InvalidArgumentException $invalidArgumentException) {
                    continue;
                }
            }
            if ($tagsToKeepIds) {
                try {
                    $this->tripTagService->deleteTagsExcept($id, $tagsToKeepIds);
                } catch (InvalidArgumentException $exception) {

                }
            }
        } else {
            $this->tripTagService->deleteAllTripTags($id);
        }
        return redirect("trip/list");
    }

    /**
     * Get all cities names indexed with cities id
     * @return array
     */
    private function getAllLocations() : array
    {
        $cities = $this->cityService->getAllCities();
        $entities = [];
        /**
         * @var City $city
         */
        foreach ($cities as $city) {
            $entities[$city->id] = $city->name;
        }
        return $entities;
    }

    /**
     * Get all activities names indexed with activities id
     * @return array
     */
    private function getAllActivities() : array
    {
        $activities = $this->activityService->getAllActivities();
        $entities = [];
        /**
         * @var Activity $activity
         */
        foreach ($activities as $activity) {
            $entities[$activity->id] = $activity->name;
        }
        return $entities;
    }

    /**
     * Get all activities names indexed with activities id
     * @return array
     */
    private function getAllTourGuideLanguages() : array
    {
        $tourGuideLanguages = $this->tourGuideLanguageService->getAllTourGuideLanguages();
        $entities = [];
        /**
         * @var TourGuideLanguage $tourGuideLanguage
         */
        foreach ($tourGuideLanguages as $tourGuideLanguage) {
            $entities[$tourGuideLanguage->id] = $tourGuideLanguage->language;
        }
        return $entities;
    }

    /**
     * Get all currencies names indexed with currencies id
     * @return array
     */
    private function getAllCurrencies() : array
    {
        $entities = [];
        $currencies = $this->currencyService->getAllCurrencies();
        /**
         * @var Currency $currency
         */
        foreach ($currencies as $currency) {
            $entities[$currency->id] = $currency->currency;
        }
        return $entities;
    }

    /**
     * Map request data to @see Trip instance
     * @param array $data
     * @return Trip
     */
    private function mapDataToTripEntity(array $data) : Trip
    {
        $trip = new Trip();
        $tripCommonDataTranslations = [];
        if (isset($data["default-name"])) {
            $trip->name = $data["default-name"];
        }
        if (isset($data["photo"])) {
            $trip->photo = $data["photo"];
        }
        if (isset($data["location"])) {
            $city = new City();
            $city->id = $data["location"];
            $trip->location = $city;
        }
        if (isset($data["adult-price"])) {
            $trip->price = $data["adult-price"];
        }
        if (isset($data["adult-price-currency"])) {
            $currency = new Currency();
            $currency->id = $data["adult-price-currency"];
            $trip->currency = $currency;
        }
        if (isset($data["activity"])) {
            $activity = new Activity();
            $activity->id = $data["activity"];
            $trip->activity = $activity;
        }
        if (isset($data["tour-guide-language"]) && is_array($data["tour-guide-language"])) {
            foreach ($data["tour-guide-language"] as $languageId) {
                $tourGuideLanguage = new TourGuideLanguage();
                $tourGuideLanguage->id = $languageId;
                $tripTourGuideLanguage = new TripTourGuideLanguage();
                $tripTourGuideLanguage->tourGuideLanguage = $tourGuideLanguage;
                $trip->tourGuideLanguages[] = $tripTourGuideLanguage;
            }
        }
        if (isset($data["confirmation-type"])) {
            $trip->confirmationType = $data["confirmation-type"];
        }
        if (isset($data["time-to-confirm"])) {
            $trip->timeToConfirm = $data["time-to-confirm"];
        }
        if (isset($data["time-to-confirm-type"])) {
            $trip->timeToConfirmType = $data["time-to-confirm-type"];
        }
        if (isset($data["start-date"])) {
            $trip->startDate = date(DateTimeFormat::MYSQL_DATE_FORMAT, strtotime($data["start-date"]));
        }
        if (isset($data["end-date"])) {
            $trip->endDate = date(DateTimeFormat::MYSQL_DATE_FORMAT, strtotime($data["end-date"]));
        }
        if (isset($data["start-time"])) {
            $trip->startTime = date(DateTimeFormat::MYSQL_TIME_FORMAT, strtotime($data["start-time"]));
        }
        if (isset($data["end-time"])) {
            $trip->endTime = date(DateTimeFormat::MYSQL_TIME_FORMAT, strtotime($data["end-time"]));
        }
        if (isset($data["lang"]) && is_array($data["lang"])) {
            foreach ($data["lang"] as $langCode => $langData) {
                $tripCommonData = new TripTranslation();
                if (isset($langData["translation-id"])) {
                    $tripCommonData->id = $langData["translation-id"];
                }
                if (isset($langData["display-name"])) {
                    $tripCommonData->displayName = $langData["display-name"];
                    $tripCommonData->languageCode = $langCode;
                }
                if (isset($langData["description"])) {
                    $tripCommonData->description = $langData["description"];
                }
                if (isset($langData["cancellation-policy"])) {
                    $tripCommonData->cancellationPolicy = $langData["cancellation-policy"];
                }
                if (isset($langData["additional-field-description"])) {
                    $tripAdditionalField = new TripAdditionalField();
                    if (isset($langData["additional-field-id"])) {
                        $tripAdditionalField->id = $langData["additional-field-id"];
                    }
                    $tripAdditionalField->title = $langData["additional-field-title"];
                    $tripAdditionalField->description = $langData["additional-field-description"];
                    $tripAdditionalField->languageCode = $langCode;
                    $trip->additionalFields[] = $tripAdditionalField;
                }
                if (isset($langData["meet-up-info"])) {
                    $tripCommonData->meetUpInfo = $langData["meet-up-info"];
                }
                if (isset($langData["package-option"])) {
                    $tripCommonData->packageOption = $langData["package-option"];
                }
                if (isset($langData["inclusive-of"])) {
                    $tripCommonData->inclusiveOf = $langData["inclusive-of"];
                }
                if (isset($langData["not-inclusive-of"])) {
                    $tripCommonData->notInclusiveOf = $langData["not-inclusive-of"];
                }
                $isTripCommonData = array_filter(get_object_vars($tripCommonData));
                if ($isTripCommonData) {
                    $tripCommonDataTranslations[] = $tripCommonData;
                }
            }
            $trip->tripDataMultiLanguages = $tripCommonDataTranslations;
        }
        if (isset($data["tags"])) {
            foreach ($data["tags"] as $tagId) {
                $tag = new Tag();
                $tag->id = $tagId;
                $tripTag = new TripTag();
                $tripTag->tag = $tag;
                $trip->tags[] = $tripTag;
            }
        }
        return $trip;
    }

    /**
     * Get trip price for child
     * @param array $data
     * @return TripPrice
     */
    private function mapDataToTripChildPrice(array $data) : TripPrice
    {
        $tripPrice = new TripPrice();
        if (
            isset($data["child-price"]) &&
            isset($data["child-age-from"]) &&
            isset($data["child-age-from-period"]) &&
            isset($data["child-age-to"]) &&
            isset($data["child-age-to-period"])
        ) {
            if (isset($data["child-price-id"])) {
                $tripPrice->id = $data["child-price-id"];
            }
            $tripPrice->type = TripPriceAgeType::CHILD_ENUM_VALUE;
            $tripPrice->price = $data["child-price"];
            $tripPrice->ageFrom = $data["child-age-from"];
            $tripPrice->ageFromPeriod = $data["child-age-from-period"];
            $tripPrice->ageTo = $data["child-age-to"];
            $tripPrice->ageToPeriod = $data["child-age-to-period"];
        }
        return $tripPrice;
    }

    /**
     * Get trip price for infants
     * @param array $data
     * @return TripPrice
     */
    private function mapDataToTripInfantPrice(array $data) : TripPrice
    {
        $tripPrice = new TripPrice();
        if (
            isset($data["infant-price"]) &&
            isset($data["infant-age-from"]) &&
            isset($data["infant-age-from-period"]) &&
            isset($data["infant-age-to"]) &&
            isset($data["infant-age-to-period"])
        ) {
            if (isset($data["infant-price-id"])) {
                $tripPrice->id = $data["infant-price-id"];
            }
            $tripPrice->type = TripPriceAgeType::INFANT_ENUM_VALUE;
            $tripPrice->price = $data["infant-price"];
            $tripPrice->ageFrom = $data["infant-age-from"];
            $tripPrice->ageFromPeriod = $data["infant-age-from-period"];
            $tripPrice->ageTo = $data["infant-age-to"];
            $tripPrice->ageToPeriod = $data["infant-age-to-period"];
        }
        return $tripPrice;
    }

    /**
     * Get array of trip events and their related prices and languages
     * @param array $data
     * @return array
     */
    private function mapDataToTripEvents(array $data) : array
    {
        $tripEvents = [];
        if (isset($data["trip-event"])) {
            foreach ($data["trip-event"] as $event) {
                $tripEvent = new TripEvent();
                if (isset($event["event-id"])) {
                    $tripEvent->id = $event["event-id"];
                }
                $tripInfantPrice = null;
                $tripChildPrice = null;
                $tripPrice = null;
                $tripPriceCurrency = null;
                $tripTourGuideLanguage = null;
                if (isset($event["adult-price"]) && isset($event["adult-price-currency"])) {
                    $tripPriceCurrency = new Currency();
                    $tripPriceCurrency->id = $event["adult-price-currency"];
                    $tripEvent->price = $event["adult-price"];
                    $tripEvent->currency = $tripPriceCurrency;
                }
                if (
                    isset($event["infant-price"]) &&
                    isset($event["infant-age-from"]) &&
                    isset($event["infant-age-from-period"]) &&
                    isset($event["infant-age-to"]) &&
                    isset($event["infant-age-to-period"])
                ) {
                    $tripInfantPrice = new TripEventPrice();
                    if (isset($event["infant-price-id"])) {
                        $tripInfantPrice->id = $event["infant-price-id"];
                    }
                    $tripInfantPrice->type = TripPriceAgeType::INFANT_ENUM_VALUE;
                    $tripInfantPrice->price = $event["infant-price"];
                    $tripInfantPrice->ageFrom = $event["infant-age-from"];
                    $tripInfantPrice->ageFromPeriod = $event["infant-age-from-period"];
                    $tripInfantPrice->ageTo = $event["infant-age-to"];
                    $tripInfantPrice->ageToPeriod = $event["infant-age-to-period"];
                    $tripEvent->infantPrice = $tripInfantPrice;
                }
                if (
                    isset($event["child-price"]) &&
                    isset($event["child-age-from"]) &&
                    isset($event["child-age-from-period"]) &&
                    isset($event["child-age-to"]) &&
                    isset($event["child-age-to-period"])
                ) {
                    $tripChildPrice = new TripEventPrice();
                    if (isset($event["child-price-id"])) {
                        $tripChildPrice->id = $event["child-price-id"];
                    }
                    $tripChildPrice->type = TripPriceAgeType::CHILD_ENUM_VALUE;
                    $tripChildPrice->price = $event["child-price"];
                    $tripChildPrice->ageFrom = $event["child-age-from"];
                    $tripChildPrice->ageFromPeriod = $event["child-age-from-period"];
                    $tripChildPrice->ageTo = $event["child-age-to"];
                    $tripChildPrice->ageToPeriod = $event["child-age-to-period"];
                    $tripEvent->childPrice = $tripChildPrice;
                }
                if (
                    isset($event["start-date"]) &&
                    isset($event["end-date"]) &&
                    isset($event["start-time"]) &&
                    isset($event["end-time"])
                ) {
                    $tripEvent->startDate =
                        date(DateTimeFormat::MYSQL_DATE_FORMAT, strtotime($event["start-date"]));
                    $tripEvent->endDate =
                        date(DateTimeFormat::MYSQL_DATE_FORMAT, strtotime($event["end-date"]));
                    $tripEvent->startTime =
                        date(DateTimeFormat::MYSQL_TIME_FORMAT, strtotime($event["start-time"]));
                    $tripEvent->endTime =
                        date(DateTimeFormat::MYSQL_TIME_FORMAT, strtotime($event["end-time"]));
                }
                if (isset($event["tour-guide-language"]) && is_array($event["tour-guide-language"])) {
                    foreach ($event["tour-guide-language"] as $languageId) {
                        $tourGuideLanguage = new TourGuideLanguage();
                        $tourGuideLanguage->id = $languageId;
                        $tripEventTourGuideLanguage = new TripEventTourGuideLanguage();
                        $tripEventTourGuideLanguage->tourGuideLanguage = $tourGuideLanguage;
                        $tripEvent->tourGuideLanguages[] = $tripEventTourGuideLanguage;
                    }
                }
                $tripEvents[] = $tripEvent;
            }
        }
        return $tripEvents;
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewTripItem (Request $request, int $id)
    {
        try {
            $existedTrip = $this->tripService->getTripWithId($id);
        } catch (NotFoundException $notFoundException) {
            return redirect("trip/list");
        }
        $availableLanguages = $this->systemAvailableLanguageService->getAllAvailableLanguages();
        $allAvailableLanguages = [];
        foreach ($availableLanguages as $availableLanguage) {
            $allAvailableLanguages[$availableLanguage->systemLanguage->code] = $availableLanguage->systemLanguage->language;
        }
        $tripTourGuideLanguages = [];
        if (isset($existedTrip->tourGuideLanguages)) {
            foreach ($existedTrip->tourGuideLanguages as $tourGuideLanguage) {
                $tripTourGuideLanguages[] = $tourGuideLanguage->tourGuideLanguage->language;
            }
        }
        $tripLanguages = [];
        if (isset($existedTrip->tripDataMultiLanguages)) {
            foreach ($existedTrip->tripDataMultiLanguages as $tripLanguage) {
                $tripLanguages[$tripLanguage->languageCode] = [
                    "translationId" => $tripLanguage->id,
                    "displayName" => ($tripLanguage->displayName) ? $tripLanguage->displayName : '',
                    "description" => ($tripLanguage->description) ? trim($tripLanguage->description) : '',
                    "packageOption" => ($tripLanguage->packageOption) ? $tripLanguage->packageOption : '',
                    "inclusiveOf" => ($tripLanguage->inclusiveOf) ? $tripLanguage->inclusiveOf : '',
                    "notInclusiveOf" => ($tripLanguage->notInclusiveOf) ? $tripLanguage->notInclusiveOf : '',
                    "meetUpInfo" => ($tripLanguage->meetUpInfo) ? $tripLanguage->meetUpInfo : '',
                    "cancellationPolicy" => ($tripLanguage->cancellationPolicy) ? $tripLanguage->cancellationPolicy : '',
                ];
            }
        }
        if (isset($existedTrip->additionalFields)) {
            foreach ($existedTrip->additionalFields as $additionalField) {
                /**
                 * @var TripAdditionalField $additionalField
                 */
                $tripLanguages[$additionalField->languageCode]["additionalField"] = [
                    "title" => isset($additionalField->title) ?
                        $additionalField->title : '',
                    "description" => isset($additionalField->description) ?
                        $additionalField->description : ''
                ];
            }
        }
        $events = [];
        if (isset($existedTrip->events)) {
            foreach ($existedTrip->events as $event) {
                $tripEventTourGuideLanguages = [];
                if (isset($event->tourGuideLanguages)) {
                    foreach ($event->tourGuideLanguages as $tourGuideLanguage) {
                        $tripEventTourGuideLanguages[] = $tourGuideLanguage->tourGuideLanguage->id;
                    }
                }
                $events[$event->id] = [
                    "startDate" => isset($event->startDate) ?
                        date(DateTimeFormat::TRIP_FORM_DATE_FORMAT, strtotime($event->startDate)) : '',
                    "startTime" => isset($event->startTime) ?
                        date(DateTimeFormat::TRIP_FORM_TIME_FORMAT, strtotime($event->startTime)) : '',
                    "endDate" => isset($event->endDate) ?
                        date(DateTimeFormat::TRIP_FORM_DATE_FORMAT, strtotime($event->endDate)) : '',
                    "endTime" => isset($event->endTime) ?
                        date(DateTimeFormat::TRIP_FORM_TIME_FORMAT, strtotime($event->endTime)) : '',
                    "price" => $event->price ? $event->price : '',
                    "currency" => isset($event->currency->id) ?
                        $event->currency->id : '',
                    "childPrice" => [
                        "id" => isset($event->childPrice->id) ? $event->childPrice->id : NULL,
                        "type" => isset($event->childPrice->type) ? TripPriceAgeType::getEnumValue($event->childPrice->type) : '',
                        "price" => isset($event->childPrice->price) ? $event->childPrice->price : '',
                        "ageFrom" => isset($event->childPrice->ageFrom) ? $event->childPrice->ageFrom : '',
                        "ageFromPeriod" => isset($event->childPrice->ageFromPeriod) ?
                            PreDefinedPeriods::getEnumValue($event->childPrice->ageFromPeriod) : '',
                        "ageTo" => isset($event->childPrice->ageTo) ? $event->childPrice->ageTo : '',
                        "ageToPeriod" => isset($event->childPrice->ageToPeriod) ?
                            PreDefinedPeriods::getEnumValue($event->childPrice->ageToPeriod) : ''
                    ],
                    "infantPrice" => [
                        "id" => isset($event->infantPrice->id) ? $event->infantPrice->id : NULL,
                        "type" => isset($event->infantPrice->type) ? TripPriceAgeType::getEnumValue($event->infantPrice->type) : '',
                        "price" => isset($event->infantPrice->price) ? $event->infantPrice->price : '',
                        "ageFrom" => isset($event->infantPrice->ageFrom) ? $event->infantPrice->ageFrom : '',
                        "ageFromPeriod" => isset($event->infantPrice->ageFromPeriod) ?
                            PreDefinedPeriods::getEnumValue($event->infantPrice->ageFromPeriod) : '',
                        "ageTo" => isset($event->infantPrice->ageTo) ? $event->infantPrice->ageTo : '',
                        "ageToPeriod" => isset($event->infantPrice->ageToPeriod) ?
                            PreDefinedPeriods::getEnumValue($event->infantPrice->ageToPeriod) : ''
                    ],
                    "tourGuideLanguages" => $tripEventTourGuideLanguages,
                ];
                unset($tripEventTourGuideLanguages);
            }
        }
        $tripTags = [];
        if (isset($existedTrip->tags)) {
            foreach ($existedTrip->tags as $tripTag) {
                $tripTags[] = $tripTag->tag->name;
            }
        }
        $gallery = [];
        if (isset($existedTrip->gallery)) {
            foreach ($existedTrip->gallery as $galleryImage) {
                $gallery[] = $galleryImage->photoUrl;
            }
        }
        $data = [
            "availableLanguages" => $allAvailableLanguages,
            "trip" => [
                "id" => $existedTrip->id,
                "photo" => $existedTrip->photoUrl,
                "gallery" => $gallery,
                "name" => $existedTrip->name ? $existedTrip->name : '',
                "destination" => isset($existedTrip->location->name) ? $existedTrip->location->name : NULL,
                "currency" => isset($existedTrip->currency->code) ?
                    $existedTrip->currency->code : '',
                "price" => $existedTrip->price ? $existedTrip->price : '',
                "childPrice" => [
                    "id" => isset($existedTrip->childPrice->id) ? $existedTrip->childPrice->id : NULL,
                    "type" => isset($existedTrip->childPrice->type) ? $existedTrip->childPrice->type : '',
                    "price" => isset($existedTrip->childPrice->price) ? $existedTrip->childPrice->price : '',
                    "ageFrom" => isset($existedTrip->childPrice->ageFrom) ? $existedTrip->childPrice->ageFrom : '',
                    "ageFromPeriod" => isset($existedTrip->childPrice->ageFromPeriod) ?
                        PreDefinedPeriods::getEnumValue($existedTrip->childPrice->ageFromPeriod) : '',
                    "ageTo" => isset($existedTrip->childPrice->ageTo) ? $existedTrip->childPrice->ageTo : '',
                    "ageToPeriod" => isset($existedTrip->childPrice->ageToPeriod) ?
                        PreDefinedPeriods::getEnumValue($existedTrip->childPrice->ageToPeriod) : ''
                ],
                "infantPrice" => [
                    "id" => isset($existedTrip->infantPrice->id) ? $existedTrip->infantPrice->id : NULL,
                    "type" => isset($existedTrip->infantPrice->type) ? $existedTrip->infantPrice->type : '',
                    "price" => isset($existedTrip->infantPrice->price) ? $existedTrip->infantPrice->price : '',
                    "ageFrom" => isset($existedTrip->infantPrice->ageFrom) ? $existedTrip->infantPrice->ageFrom : '',
                    "ageFromPeriod" => isset($existedTrip->infantPrice->ageFromPeriod) ?
                        PreDefinedPeriods::getEnumValue($existedTrip->infantPrice->ageFromPeriod) : '',
                    "ageTo" => isset($existedTrip->infantPrice->ageTo) ? $existedTrip->infantPrice->ageTo : '',
                    "ageToPeriod" => isset($existedTrip->infantPrice->ageToPeriod) ?
                        PreDefinedPeriods::getEnumValue($existedTrip->infantPrice->ageToPeriod) : ''
                ],
                "activity" => isset($existedTrip->activity->id) ?
                    $existedTrip->activity->id : '',
                "confirmationType" => isset($existedTrip->confirmationType) ?
                    ConfirmationType::getEnumValue($existedTrip->confirmationType) : '',
                "timeToConfirm" => isset($existedTrip->timeToConfirm) ? $existedTrip->timeToConfirm : '',
                "timeToConfirmType" => isset($existedTrip->timeToConfirmType) ?
                    PreDefinedPeriods::getEnumValue($existedTrip->timeToConfirmType) : '',
                "startDate" => isset($existedTrip->startDate) ?
                    date(DateTimeFormat::TRIP_FORM_DATE_FORMAT, strtotime($existedTrip->startDate)) : '',
                "startTime" => isset($existedTrip->startTime) ?
                    date(DateTimeFormat::TRIP_FORM_TIME_FORMAT, strtotime($existedTrip->startTime)) : '',
                "endDate" => isset($existedTrip->endDate) ?
                    date(DateTimeFormat::TRIP_FORM_DATE_FORMAT, strtotime($existedTrip->endDate)) : '',
                "endTime" => isset($existedTrip->endTime) ?
                    date(DateTimeFormat::TRIP_FORM_TIME_FORMAT, strtotime($existedTrip->endTime)) : '',
                "tourGuideLanguages" => $tripTourGuideLanguages,
                "tripDataMultiLanguages" => $tripLanguages,
                "events" => $events,
                "tags" => $tripTags
            ]
        ];
        return view("front.trip.item-view", $data);
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
        $trips = $this->tripService->getTripsWithCriteria($criteria, 1, $offset + $paging->count);
        if (empty($trips)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }

    /**
     * Crop image ajax response
     * @param Request $request
     * @return string
     */
    public function cropImage(Request $request)
    {
        $image = $request->get("image");
        $response = '';
        if ($image) {
            $data = $image;
            $imageArray1 = explode(";", $data);
            $imageArray2 = explode(",", $imageArray1[1]);
            $data = base64_decode($imageArray2[1]);
            $imageName = Str::random(40);
            $photoPath = UploadPaths::TEMP_TRIP_IMAGE_CROP_PATH . '/' . $imageName;
            Storage::put(strval($photoPath), $data);
            $photoUrl = asset("cdn/" . $photoPath);
            $response = "<input type='hidden' name='crop-image-temp-path' value='{$photoPath}'>
                    <img src='{$photoUrl}' class='file-preview-image' title='{$imageName}' alt='{$imageName}' />";
        }
        return $response;
    }

    /**
     * Delete gallery image in trip edit form
     * @param Request $request
     */
    public function deleteFormTripGalleryImage(Request $request)
    {
        $galleryId = $request->get("gallery-id", '');
        if (!$galleryId) {
            return;
        }
        if ($request->isXmlHttpRequest()) {
            $gallery = $this->tripGalleryImageService->getTripGalleryImageWithId($galleryId);
            if ($gallery) {
                Storage::delete($gallery->photo);
            }
            $this->tripGalleryImageService->deleteTripGalleryImage($galleryId);
        }
    }
}
