<?php

namespace App\Http\Controllers\Api\TripController;

use App\Http\Controllers\Abstracts\AbstractApiController;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Domain\Entity\RecentViewedTrips;
use Dev\Domain\Entity\Trip;
use Dev\Domain\Entity\User;
use Dev\Domain\Service\TripService\RecentViewedTripService;
use Dev\Domain\Service\TripService\TripService;
use Illuminate\Http\Request;
use JWTAuth;

/**
 * RecentlyViewedTripsController Class responsible for all actions related to recent viewed trips api requests
 * @package App\Http\Controllers\Api\TripController
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class RecentlyViewedTripsController extends AbstractApiController
{
    /**
     * @var RecentViewedTripService $recentViewedTripService instance from RecentViewedTripService
     */
    private $recentViewedTripService;

    /**
     * @var TripService $tripService instance from TripService
     */
    private $tripService;

    /**
     * @var int $limit (number of items per page)
     */
    private $limit = 6;

    /**
     * RecentlyViewedTripsController constructor.
     * @param Request $request
     * @param RecentViewedTripService $recentViewedTripService
     * @param TripService $tripService
     */
    public function __construct(
        Request $request,
        RecentViewedTripService $recentViewedTripService,
        TripService $tripService
    ) {
        parent::__construct($request);
        $this->recentViewedTripService = $recentViewedTripService;
        $this->tripService = $tripService;

    }

    /**
     * Get recently viewed trips list items
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $user = auth("api")->user();

        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->recentViewedTripService->setRequestObject($requestedObject);
        }
        $criteria   = [
            "user-id" => $user->id,
        ];
        $page       = intval($request->get('page', 1));
        $limit      = intval($request->get('limit', $this->limit));
        $offset     = ($page - 1) * $limit;

        $tripWishListWithCriteria    = $this->recentViewedTripService->getRecentViewedTripRecordWithCriteria($criteria, $limit, $offset);
        if ($tripWishListWithCriteria) {
            $responseData["data"]  = $tripWishListWithCriteria;
            $responseData["paging"] = $this->getWishListPaging($criteria, $limit, $offset);
            return response()->json($responseData, 200);
        } else {
            return response()->json('', 204);
        }
    }

    /**
     * add a trip to user recent viewed list
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Dev\Application\Exceptions\InvalidArgumentException
     * @throws \Dev\Application\Exceptions\NotFoundException
     */
    public function AddTripRecentViewedList(Request $request)
    {
        $user = auth("api")->user();
        $data = $request->all();
        $errors = [];
        if (!isset($data['trip_id'])) {
            $errors["errors"]["trip_id"] = "Must send trip id";
        }
        if ($errors) {
            return response($errors, 400);
        }

        $tripItem = $this->tripService->getTripWithId($data['trip_id']);
        if ($tripItem){
            $recentViewedTrips = new RecentViewedTrips();
            $newUser = new User();
            $newUser->id = $user->id;
            $recentViewedTrips->user = $newUser;
            $newTrip = new Trip();
            $newTrip->id = $data['trip_id'];
            $recentViewedTrips->trip = $newTrip;
            try{
               $this->recentViewedTripService->createRecentViewedTripRecord($recentViewedTrips);
            }catch (InvalidArgumentException $invalidArgumentException){
                $errors["errors"]["trip"] = "There was an error while adding the trip to the user recently viewed list";
                return response()->json($errors, 422 );
            }
                return response()->json("", 204);
        }
        else{
            $errors["errors"]["trip_id"] = "no trip with the sent ID";
            return response()->json($errors, 404 );
        }
    }

    /**
     * Return paging url response
     * @param array $criteria
     * @param int|null $count
     * @param int|null $offset
     * @return \stdClass
     */
    private function getWishListPaging(array $criteria, int $count = null, int $offset = null)
    {
        $paging = parent::initializePaging($count, $offset);
        $recentViewedTripRecordWithCriteria = $this->recentViewedTripService->getRecentViewedTripRecordWithCriteria($criteria, 1, $offset + $paging->count);
        if (empty($recentViewedTripRecordWithCriteria)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }
}