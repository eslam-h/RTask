<?php

namespace App\Http\Controllers\Api\TripController;

use App\Http\Controllers\Abstracts\AbstractApiController;
use Dev\Application\Exceptions\NotFoundException;
use Dev\Application\Exceptions\TripLanguageNotFoundException;
use Dev\Domain\Service\TripService\TripService;
use Dev\Domain\Service\TagService\TagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Dev\Infrastructure\Models\TripModels\TripTagModel;

/**
 * TripController Class responsible for all actions related to trip api requests
 * @package App\Http\Controllers\Api\TripController
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class TripController extends AbstractApiController
{
    /**
     * @var TripService $tripService instance from TripService
     */
    private $tripService;


    private $tagService;

    /**
     * TripController constructor.
     * @param TripService $tripService instance from TripService
     * @param Request $request instance from Request
     */
    public function __construct(Request $request, TripService $tripService, TagService $tagService)
    {
        parent::__construct($request);
        $this->tripService = $tripService;
        $this->tagService  = $tagService;
    }

    /**
     * Get trip item details
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewTripItem(Request $request, int $id)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->tripService->setRequestObject($requestedObject);
        }

        $platformLanguage = $request->header("Platform-Language", "en");
        try {
            $trip = $this->tripService->getTripWithSpecifiedLanguage($id, $platformLanguage);
        } catch (NotFoundException $notFoundException) {
            $errors["errors"]["trip"] = "Trip not found with specified id";
        } catch (TripLanguageNotFoundException $notFoundException) {
            $errors["errors"]["trip"] = "Trip not found with specified language";
        }
        if (isset($errors)) {
            return response($errors, 422);
        }
        $responseData["data"] = $trip;
        return response()->json($responseData, 200);
    }

    /**
     * Get trip items list
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->tripService->setRequestObject($requestedObject);
        }
        $criteria = [];
        $page     = $request->has('page')? abs((int) $request->get('page')) : 1;
        $limit    = $request->has('limit')? abs((int) $request->get('limit')) : 5;
        $offset   = ($page - 1) * $limit;
        if ($request->has('priceFrom')) {
            $criteria['price-from'] = (int) $request->get('priceFrom');
        }
        if ($request->has('priceTo')) {
            $criteria['price-to'] = (int) $request->get('priceTo');
        }

###############################################################################
        if ($request->has('dateFrom')) {
            $criteria['date-from'] = $request->get('dateFrom');
        }
        if ($request->has('dateTo')) {
            $criteria['date-to'] =  $request->get('dateTo');
        }

        if ($request->has('cityIds')) {
            $criteria['city-ids'] = $request->get('cityIds');
        }
        if ($request->has('activityIds')) {
            $criteria['activity-ids'] = $request->get('activityIds');
        }
###############################################################################

        if ($request->has('tagIds')) {
            $criteria['tag-ids'] = $request->get('tagIds');
        }
        $trips    = $this->tripService->getTripsWithCriteria($criteria, $limit, $offset);
        $results  = [];
        if ($trips) {
            foreach ($trips as $trip) {
                $results[] = array(
                            'id'            => $trip->id,
                            'name'          => $trip->name,
                            'photo'         => $trip->photoUrl,
                            'price'         => $trip->price,
                            'currency'      => 'EGP',
                            'oldPrice'      => rand (($trip->price + 200) , ($trip->price + 500) ),
                            'rate'          => rand(1,5),
                            'rateCount'     => rand(5,20),
                            'booked'        => rand(0,30),
                            //'availableFrom' => '2019-0'.rand(6,7).'-'.rand(25,30),
                            'availableFrom' => $trip->startDate . ' ' . $trip->startTime,
                           );
            }
        } else {
            return response()->json('', 204);
        }

        //$tags = $this->tagService->getTagWithCriteria();

        $responseData["data"]  = $results;
        $responseData['total'] = count($this->tripService->getTripsWithCriteria($criteria,999999));
        // $responseData['tags'] = $tags;
        // $responseData['trip-tags'] = TripTagModel::all()->toArray();
        // $responseData['ttt'] = TripTagModel::tags();


        $tags = TripTagModel::join('tags', 'trip-tags.tag-id', '=', 'tags.id')
                            ->select('tags.id', 'tags.name')
                            ->distinct()
                            ->orderBy('tags.name', 'asc')
                            ->get();
        //$responseData["tags"] = $tags;
        return response()->json($responseData, 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mostViewed(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->tripService->setRequestObject($requestedObject);
        }
        $results = $this->tripService->getMostViewedTrips();
        if ($results) {
            $responseData["data"] = $results;
            return response()->json($responseData, 200);
        } else {
            return response()->json('', 204);
        }
    }
    
}