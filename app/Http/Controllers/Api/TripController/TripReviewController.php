<?php

namespace App\Http\Controllers\Api\TripController;

use App\Http\Controllers\Abstracts\AbstractApiController;
use Dev\Application\Exceptions\InvalidArgumentException;
use Dev\Domain\Entity\Trip;
use Dev\Domain\Entity\TripComment;
use Dev\Domain\Entity\TripRate;
use Dev\Domain\Entity\User;
use Dev\Domain\Service\TripService\TripCommentService;
use Dev\Domain\Service\TripService\TripRateService;
use Dev\Domain\Service\TripService\TripService;
use Illuminate\Http\Request;
use JWTAuth;

/**
 * TripReviewController Class responsible for all actions related to trip review api requests
 * @package App\Http\Controllers\Api\TripController
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class TripReviewController extends AbstractApiController
{
    /**
     * @var TripRateService $tripRateService instance from TripRateService
     */
    private $tripRateService;

    /**
     * @var TripCommentService $tripCommentService instance from TripCommentService
     */
    private $tripCommentService;

    /**
     * @var TripService $tripService instance from TripService
     */
    private $tripService;

    /**
     * @var int $limit (number of items per page)
     */
    private $limit = 10;

    /**
     * TripReviewController constructor.
     * @param Request $request
     * @param TripCommentService $tripCommentService
     * @param TripRateService $tripRateService
     * @param TripService $tripService
     */
    public function __construct(
        Request $request,
        TripCommentService $tripCommentService,
        TripRateService $tripRateService,
        TripService $tripService
    )
    {
        parent::__construct($request);
        $this->tripRateService = $tripRateService;
        $this->tripService = $tripService;
        $this->tripCommentService = $tripCommentService;

    }

    public function list(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->tripRateService->setRequestObject($requestedObject);
        }
        $user = auth("api")->user();
        $data = $request->all();
        $errors = [];
        if (!isset($data['trip_id'])) {
            $errors["errors"]["trip_id"] = "Must send trip id";
        }
        if ($errors) {
            return response($errors, 400);
        }
        $criteria = [
            "user-id" => $user->id,
            "trip-id" => $data['trip_id']
        ];

        $page = intval($request->get('page', 1));
        $limit = intval($request->get('limit', $this->limit));
        $offset = ($page - 1) * $limit;

        $tripReviews    = $this->tripRateService->getTripRateWithCriteria($criteria, $limit, $offset);
        $paging    = $this->getTripReviewPaging($criteria, $limit, $offset);
        if ($tripReviews) {
            $responseData["data"]  = $tripReviews;
            $responseData["paging"]  = $paging;
            return response()->json($responseData, 200);
        } else {
            return response()->json('', 204);
        }
    }

    /**
     * add a trip review
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Dev\Application\Exceptions\InvalidArgumentException
     * @throws \Dev\Application\Exceptions\NotFoundException
     */
    public function AddTripReview(Request $request)
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
        if (!isset($data['rate']) && !isset($data['comment'])) {
            $errors["errors"]["data"] = "Must send either rate or comment.";
        }
        if ($errors) {
            return response($errors, 400);
        }
        $tripItem = $this->tripService->getTripWithId($data['trip_id']);
        if ($tripItem) {

            if (isset($data['rate'])) {
                if ($data['rate'] >= 1 && $data['rate'] <= 5) {
                    $criteria =
                        ["user-id" => $user->id, "trip-id" => $data['trip_id']];
                    $previousRating = $this->tripRateService->getTripRateWithCriteria($criteria);
                    if ($previousRating) {
                        $errors["errors"]["trip_id"] = "User already rated this trip";
                        return response()->json($errors, 422);
                    }

                    $tripComment = new TripRate();
                    $newUser = new User();
                    $newUser->id = $user->id;
                    $tripComment->user = $newUser;
                    $newTrip = new Trip();
                    $newTrip->id = $data['trip_id'];
                    $tripComment->trip = $newTrip;
                    $tripComment->rate = $data['rate'];

                    try {
                        $this->tripRateService->createTripRate($tripComment);
                    } catch (InvalidArgumentException $invalidArgumentException) {
                        $errors["errors"]["trip"] = "There was an error while adding the trip rate";
                        return response()->json($errors, 422);
                    }
                } else {
                    $errors["errors"]["rate"] = "Rate must be between 1 and 5";
                    return response()->json($errors, 422);
                }
            }
            if (isset($data['comment'])) {
                $criteria =
                    ["user-id" => $user->id, "trip-id" => $data['trip_id']];
                $previousRating = $this->tripCommentService->getTripCommentWithCriteria($criteria);
                if ($previousRating) {
                    $errors["errors"]["trip_id"] = "User already commented on this trip";
                    return response()->json($errors, 422);
                }

                $tripComment = new TripComment();
                $newUser = new User();
                $newUser->id = $user->id;
                $tripComment->user = $newUser;
                $newTrip = new Trip();
                $newTrip->id = $data['trip_id'];
                $tripComment->trip = $newTrip;
                $tripComment->comment = $data['comment'];

                try {
                    $this->tripCommentService->createTripComment($tripComment);
                } catch (InvalidArgumentException $invalidArgumentException) {
                    $errors["errors"]["trip"] = "There was an error while adding the trip comment";
                    return response()->json($errors, 422);
                }
            }

            return response()->json("", 204);
        } else {
            $errors["errors"]["trip_id"] = "no trip with the sent ID";
            return response()->json($errors, 404);
        }
    }

    /**
     * Return paging url response
     * @param array $criteria
     * @param int|null $count
     * @param int|null $offset
     * @return \stdClass
     */
    private function getTripReviewPaging(array $criteria, int $count = null, int $offset = null)
    {
        $paging = parent::initializePaging($count, $offset);
        $tripReviews = $this->tripRateService->getTripRateWithCriteria($criteria, 1, $offset + $paging->count);
        if (empty($tripReviews)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }
}