<?php

namespace App\Http\Controllers\Api\TripController;

use App\Http\Controllers\Abstracts\AbstractApiController;
use Dev\Domain\Service\TripService\TripBookingService;
use Illuminate\Http\Request;
use JWTAuth;

/**
 * TripBookingController Class responsible for all actions related to trip booking api requests
 * @package App\Http\Controllers\Api\TripController
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class TripBookingController extends AbstractApiController
{
    /**
     * @var TripBookingService $tripBookingService instance from TripBookingService
     */
    private $tripBookingService;

    /**
     * @var int $limit (number of items per page)
     */
    private $limit = 10;

    /**
     * TripBookingController constructor.
     * @param TripBookingService $tripBookingService instance from TripBookingService
     * @param Request $request instance from Request
     */
    public function __construct(
        Request $request,
        TripBookingService $tripBookingService
    ) {
        parent::__construct($request);
        $this->tripBookingService = $tripBookingService;
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
            $this->tripBookingService->setRequestObject($requestedObject);
        }
        $user = auth("api")->user();
        $criteria   = [
            "user-id" => $user->id,
            "confirmed" => true
        ];

        $page       = intval($request->get('page', 1));
        $limit      = intval($request->get('limit', $this->limit));
        $offset     = ($page - 1) * $limit;
        $sort = "ASC";
        $tripBookings    = $this->tripBookingService->getTripBookingsWithCriteria($criteria, $limit, $offset, $sort);
        if ($tripBookings) {
            $responseData["data"]  = $tripBookings;
            return response()->json($responseData, 200);
        } else {
            return response()->json('', 204);
        }
    }
}