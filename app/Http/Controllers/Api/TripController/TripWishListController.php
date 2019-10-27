<?php

namespace App\Http\Controllers\Api\TripController;

use App\Http\Controllers\Abstracts\AbstractApiController;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Domain\Entity\Trip;
use Dev\Domain\Entity\TripWishList;
use Dev\Domain\Entity\User;
use Dev\Domain\Service\TripService\TripBookingService;
use Dev\Domain\Service\TripService\TripService;
use Dev\Domain\Service\TripService\TripWishListService;
use Illuminate\Http\Request;
use JWTAuth;

/**
 * TripWishListController Class responsible for all actions related to trip wish list api requests
 * @package App\Http\Controllers\Api\TripController
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class TripWishListController extends AbstractApiController
{
    /**
     * @var TripWishListService $tripWishListService instance from TripWishListService
     */
    private $tripWishListService;

    /**
     * @var TripService $tripService instance from TripService
     */
    private $tripService;

    /**
     * @var int $limit (number of items per page)
     */
    private $limit = 10;

    /**
     * TripWishListController constructor.
     * @param Request $request
     * @param TripWishListService $tripWishListService
     * @param TripService $tripService
     */
    public function __construct(
        Request $request,
        TripWishListService $tripWishListService,
        TripService $tripService
    ) {
        parent::__construct($request);
        $this->tripWishListService = $tripWishListService;
        $this->tripService = $tripService;

    }

    /**
     * Get trip wish list items
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->tripWishListService->setRequestObject($requestedObject);
        }
        $user = auth("api")->user();
        $criteria   = [
            "user-id" => $user->id,
        ];
        $page       = intval($request->get('page', 1));
        $limit      = intval($request->get('limit', $this->limit));
        $offset     = ($page - 1) * $limit;

        $tripWishListWithCriteria    = $this->tripWishListService->getTripWishListWithCriteria($criteria, $limit, $offset);
        if ($tripWishListWithCriteria) {
            $responseData["data"]  = $tripWishListWithCriteria;
            $responseData["paging"] = $this->getWishListPaging($criteria, $limit, $offset);
            return response()->json($responseData, 200);
        } else {
            return response()->json('', 204);
        }
    }

    /**
     * add a trip to user wish list
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Dev\Application\Exceptions\InvalidArgumentException
     * @throws \Dev\Application\Exceptions\NotFoundException
     */
    public function AddTripToWishList(Request $request)
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
            $tripWishListEntity = new TripWishList();
            $newUser = new User();
            $newUser->id = $user->id;
            $tripWishListEntity->user = $newUser;
            $newTrip = new Trip();
            $newTrip->id = $data['trip_id'];
            $tripWishListEntity->trip = $newTrip;

            $criteria = ["user-id" => $user->id,
                "trip-id" =>$data['trip_id'] ];
            $userWishListTrip = $this->tripWishListService->getTripWishListWithCriteria($criteria);
            if ($userWishListTrip){
                $errors["errors"]["trip_id"] = "Trip already added to user wish list";
                return response()->json($errors, 422 );
            }
            try{
               $this->tripWishListService->createTripWishList($tripWishListEntity);
            }catch (InvalidArgumentException $invalidArgumentException){
                $errors["errors"]["trip"] = "There was an error while adding the trip to the user wish list";
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
     * remove a trip from user wish list
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Dev\Application\Exceptions\InvalidArgumentException
     * @throws \Dev\Application\Exceptions\NotFoundException
     */
    public function RemoveTripFromWishList(Request $request)
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
            $criteria = ["user-id" => $user->id,
                         "trip-id" =>$data['trip_id'] ];
                $userWishListTrip = $this->tripWishListService->getTripWishListWithCriteria($criteria);
                if ($userWishListTrip){
                    $this->tripWishListService->deleteTripWishList($userWishListTrip[0]->id);
                    return response()->json("", 204);
                } else{
                $errors["errors"]["trip"] = "This trip is not in the user wish list";
                return response()->json($errors, 422 );
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
        $tripWishListWithCriteria = $this->tripWishListService->getTripWishListWithCriteria($criteria, 1, $offset + $paging->count);
        if (empty($tripWishListWithCriteria)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }
}