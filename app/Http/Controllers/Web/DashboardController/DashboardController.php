<?php


namespace App\Http\Controllers\Web\DashboardController;

use App\Http\Controllers\Abstracts\AbstractController;
use Dev\Domain\Entity\Activity;
use Dev\Domain\Entity\City;
use Dev\Domain\Service\ActivityService\ActivityService;
use Dev\Domain\Service\CityService\CityService;
use Dev\Domain\Service\DashboardService\DashboardService;
use Dev\Domain\Utility\DateTimeFormat;
use Illuminate\Http\Request;

/**
 * DashboardController Class responsible for all dashboard functionality
 * @package App\Http\Controllers\Web\DashboardController
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class DashboardController extends AbstractController
{
    private $dashboardService;
    private $cityService;
    private $activityService;
    /**
     * @var int page count
     */
    private $count = 30;
    public function __construct(Request $request,
                                DashboardService $dashboardService,
ActivityService $activityService,
CityService $cityService)
    {
        parent::__construct($request);
        $this->dashboardService = $dashboardService;
        $this->activityService = $activityService;
        $this->cityService = $cityService;

    }

    /**
     * Responsible for displaying dashboard
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function displayDashboard()
    {
        $data = $this->getVisitedBookingChartData();
        $data['supplier'] = $this->getSupplierBookedTrips();
        $data = array_merge($data, $this->getTripRateChartData(), $this->getUpcomingTrips());
        return view("front.dashboard.dashboard", $data);
    }

    /**
     * Get data for visited and booking chart
     * @return array
     */
    private function getVisitedBookingChartData()
    {
        $thisMonthDateFrom = date(DateTimeFormat::FIRST_DAY_IN_THE_MONTH);
        $thisMonthDateTo = date(DateTimeFormat::DEFAULT_DATE_FORMAT);

        $lastMonthDateFrom = date(DateTimeFormat::FIRST_DAY_IN_THE_MONTH, strtotime("-1 month"));
        $lastMonthDateTo = date(DateTimeFormat::LAST_DAY_IN_THE_MONTH, strtotime("-1 month"));

        $monthBeforeLastMonthDateFrom = date(DateTimeFormat::FIRST_DAY_IN_THE_MONTH, strtotime("-2 month"));
        $monthBeforeLastMonthDateTo = date(DateTimeFormat::LAST_DAY_IN_THE_MONTH, strtotime("-2 month"));


        $numberOfVisitedTripsThisMonth =
            $this->dashboardService->getNumberOfVisitedTrips($thisMonthDateFrom, $thisMonthDateTo);

        $numberOfVisitedTripsTwoMonth =
            $this->dashboardService->getNumberOfVisitedTrips($lastMonthDateFrom, $lastMonthDateTo);

        $numberOfVisitedTripsMonthBeforeLastMonth =
            $this->dashboardService->getNumberOfVisitedTrips($monthBeforeLastMonthDateFrom, $monthBeforeLastMonthDateTo);

        $numberOfBookingsThisMonth =
            $this->dashboardService->getNumberOfBookedTrips($thisMonthDateFrom, $thisMonthDateTo);
        $numberOfBookingsLastMonth =
            $this->dashboardService->getNumberOfBookedTrips($lastMonthDateFrom, $lastMonthDateTo);
        $numberOfBookingsMonthBeforeLastMonth =
            $this->dashboardService->getNumberOfBookedTrips($monthBeforeLastMonthDateFrom, $monthBeforeLastMonthDateTo);
        $user = auth()->user();

        $tripsAverageRate = $this->dashboardService->getAverageTripsRate($user->id);
        $tripsRateOccurence = $this->dashboardService->getNumberOfRatesPerRate($user->id);
//        dd($tripsRateOccurence);

        $data = [
            "numberOfVisitedTrips" => [
                $numberOfVisitedTripsThisMonth,
                $numberOfVisitedTripsTwoMonth,
                $numberOfVisitedTripsMonthBeforeLastMonth
            ],
            "numberOfBookings" => [
                $numberOfBookingsThisMonth,
                $numberOfBookingsLastMonth,
                $numberOfBookingsMonthBeforeLastMonth
            ]
        ];
        return $data;
    }


    private function getSupplierBookedTrips()
    {
        $uid = auth()->user()->id;
        $bookedTrips = $this->dashboardService->getBookoedTripsBySupplierId($uid);
        $totalPrice =0;
        if ($bookedTrips) {
            foreach ($bookedTrips as $trip) {
                $totalPrice += $trip->{'total-price'};
            }
        }
        $totalPrice = number_format($totalPrice, 2);
        $output = array('total-order' => count($bookedTrips), 'total-profit' => $totalPrice);
        return $output;
    }


    private function getTripRateChartData(){
        $user = auth()->user();

        $tripsAverageRate = $this->dashboardService->getAverageTripsRate($user->id);
        $tripsRateOccurence = $this->dashboardService->getNumberOfRatesPerRate($user->id);


        $data = [
            "tripsRateOccurence" => [
                '1' => (isset($tripsRateOccurence['1'])) ? $tripsRateOccurence['1'] : 0,
                '2' => (isset($tripsRateOccurence['2'])) ? $tripsRateOccurence['2'] : 0,
                '3' => (isset($tripsRateOccurence['3'])) ? $tripsRateOccurence['3'] : 0,
                '4' => (isset($tripsRateOccurence['4'])) ? $tripsRateOccurence['4'] : 0,
                '5' => (isset($tripsRateOccurence['5'])) ? $tripsRateOccurence['5'] : 0,
            ],
            "averageRate" => $tripsAverageRate
        ];
        return $data;
    }

    private function getUpcomingTrips()
    {
        $criteria = [];
        $user = auth()->user();
        $upcomingTrips['upcomingTrips'] = $this->dashboardService->getUpcomingTrips($criteria, $user->id);
        return $upcomingTrips;
    }

    public function listUpcomingTrips(Request $request)
    {
        $requestData = $request->all();
        $criteria = [];
        $page         = $request->has('page')? abs((int) $request->get('page')) : 1;
        $count        = $request->has('limit')? abs((int) $request->get('limit')) : $this->count;
        $offset       = ($page - 1) * $count;
        $user = auth()->user();

        $criteria ['created-by'] = $user->id;
        if (isset($requestData["name-search"])) {
            $criteria["name-filter"] = $requestData["name-search"];
        }
        if (isset($requestData["city-id"])) {
            $criteria["city-ids"][] = $requestData["city-id"];
        }
        if (isset($requestData["activity-id"])) {
            $criteria["activity-ids"][] = $requestData["activity-id"];
        }

        $cities = $this->getAllLocations();
        $activities = $this->getAllActivities();

        $trips = $this->dashboardService->getUpcomingTrips($criteria);

        $paging = $this->dashboardService->getPaginationLinks($criteria, $count);

        $data = [];
        foreach ($trips as $trip) {
            $tripData = [
                "id" => $trip->id,
                "name" => $trip->name,
                "photo" => $trip->photoUrl,
            ];
            $data["entities"][] = $tripData;
        }
        $data ['searchInput'] = isset($requestData['name-search']) ? $requestData['name-search'] : '';
        $data ['activityInput'] = isset($requestData['activity-id']) ? $requestData['activity-id'] : '';
        $data ['cityInput'] = isset($requestData['city-id']) ? $requestData['city-id'] : '';
        $data ['paging'] = $paging;
        $data ['activities'] = $activities;
        $data ['cities'] = $cities;
        $data['pageName'] = "Upcoming Trips listing";
        return view("front.trip.list", $data);
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
}