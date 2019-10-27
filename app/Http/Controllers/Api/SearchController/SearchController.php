<?php

namespace App\Http\Controllers\Api\SearchController;

use App\Http\Controllers\Abstracts\AbstractController;
use App\Utility\Paging;
use Dev\Domain\Service\ActivityService\ActivitySearchService;
use Dev\Domain\Utility\DateTimeFormat;
use Illuminate\Http\Request;

/**
 * SearchController Class responsible for search actions
 * @package App\Http\Controllers\Api\SearchController
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class SearchController extends AbstractController
{
    /**
     * @var ActivitySearchService $activitySearchService instance from ActivitySearchService
     */
    private $activitySearchService;

    /**
     * SearchController constructor.
     * @param Request $request instance from Request instance
     * @param ActivitySearchService $activitySearchService instance from ActivitySearchService
     */
    public function __construct(Request $request, ActivitySearchService $activitySearchService)
    {
        parent::__construct($request);
        $this->activitySearchService = $activitySearchService;
    }

    /**
     * Search for activities according to specified criteria
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchActivity(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->activitySearchService->setRequestObject($requestedObject);
        }

        $criteria     = [];
        $destinations = array_filter($request->get("destination", []));
        $startDate    = $request->get("start_date", '');
        $startDate    = \DateTime::createFromFormat(DateTimeFormat::DEFAULT_DATE_FORMAT, $startDate);
        $endDate      = $request->get("end_date", '');
        $page         = $request->has('page')? abs((int) $request->get('page')) : 1;
        $count        = $request->has('limit')? abs((int) $request->get('limit')) : Paging::DEFAULT_COUNT;
        $offset       = ($page - 1) * $count;

        if (is_array($destinations) && array_values($destinations)) {
            $criteria["city-id"] = $destinations;
        }
        if ($startDate) {
            $criteria["start-date"] = $startDate->format(DateTimeFormat::MYSQL_DATE_FORMAT);
        }
        if ($endDate) {
            $criteria["end-date"] = $endDate;
        }
        $activities = $this->activitySearchService->searchActivity($criteria, $count, $offset);
        $data["data"] = $activities;
        $data["paging"] = $this->getSearchActivityPaging($criteria, $count, $offset);
        if ($activities) {
            return response()->json($data, 200);
        } else {
            return response()->json($data, 204);
        }
    }

    /**
     * Return paging url response
     * @param array $criteria
     * @param int|null $count
     * @param int|null $offset
     * @return \stdClass
     */
    private function getSearchActivityPaging(array $criteria, int $count = null, int $offset = null)
    {
        $paging = parent::initializePaging($count, $offset);
        $activities = $this->activitySearchService->searchActivity($criteria, 1, $offset + $paging->count);
        if (empty($activities)) {
            $paging->noNextPaging();
        }
        return $paging->getPaging();
    }
}