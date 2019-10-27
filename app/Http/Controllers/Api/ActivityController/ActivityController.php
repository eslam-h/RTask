<?php

namespace App\Http\Controllers\Api\ActivityController;

use App\Http\Controllers\Abstracts\AbstractController;
use Dev\Application\Exceptions\NotFoundException;
use Illuminate\Http\Request;


use Dev\Domain\Entity\Activity;
use Dev\Domain\Service\ActivityService\ActivityService;

use Validator;



/**
 * ActivityController Class for returning app activity list
 * @package App\Http\Controllers\Api\ActivityController
 * @author Mohamad El-Wakeel <m.elwakeel@shiftebusiness.com>
 */
class ActivityController extends AbstractController
{

    /**
     * @var ActivityService $activityService instance from activity service
     */
    private $activityService;


    /**
     * @var  $limit (number of items per page)
     */
    private $limit = 3;

    /**
     * ActivityController constructor.
     * @param activityService $activityService instance from activity service
     * @param Request $request instance from Request
     */
	public function __construct(Request $request, ActivityService $activityService)
    {
        parent::__construct($request);
        $this->activityService = $activityService;
    }

    /**
     * List of Activities
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->activityService->setRequestObject($requestedObject);
        }
		$criteria   = [];
		$all        = $request->has('all')? abs((int) $request->get('all')) : 0;
		$page       = $request->has('page')? abs((int) $request->get('page')) : 1;
		$limit      = $request->has('limit')? abs((int) $request->get('limit')) : $this->limit;
		$offset     = ($page - 1) * $limit;

		if ($request->has('featured')) {
			$criteria['featured'] = 1; // $request->get('featured');
		}

		if ($all == 1) {
			$activities = $this->activityService->getActivityWithCriteria($criteria);
		} else {
			$activities = $this->activityService->getActivityWithCriteria($criteria, $limit, $offset);
		}

		if ($activities) {
			foreach ($activities as $activity) {
				$results[] = array(
								'id'    => $activity->id,
								'name'  => $activity->name,
								'icon'  => $activity->icon,
								'photo' => $activity->photo,
								'color' => $activity->color,
							 );
			}
		} else {
			return response()->json('', 204);
		}
		$responseData['data'] = $activities; //$results;
    	return response()->json($responseData, 200);	
    }
}