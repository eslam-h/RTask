<?php

namespace App\Http\Controllers\Api\CityController;

use App\Http\Controllers\Abstracts\AbstractController;
use Dev\Domain\Service\CityService\CityService;
use Illuminate\Http\Request;
use Validator;

/**
 * CityController Class for returning app city list
 * @package App\Http\Controllers\Api\CityController
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class CityController extends AbstractController
{

    /**
     * @var CityService $cityService instance from city service
     */
    private $cityService;


    /**
     * @var int $limit (number of items per page)
     */
    private $limit = 15;

    /**
     * CityController constructor.
     * @param CityService $cityService
     * @param Request $request
     */
	public function __construct(Request $request, CityService $cityService)
    {
        parent::__construct($request);
        $this->cityService = $cityService;
    }

    /**
     * List of Cities
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->cityService->setRequestObject($requestedObject);
        }
        $platformLanguage = $request->header("Platform-Language");

        $criteria   = [];
		$page       = intval($request->get('page', 1));
		$limit      = intval($request->get('limit', $this->limit));
		$offset     = ($page - 1) * $limit;
		$name = $request->get("name", '');
		$tags = $request->get("tag", '');
		$country = $request->get("country", '');

		if ($name) {
		    $criteria["name"] = $name;
        }
		if ($tags) {
            $criteria["tag-ids"] = explode(',', $tags);
        }
		if ($country) {
		    $criteria["country-id"] = $country;
        }
		if ($platformLanguage) {
		    $criteria["language-code"] = $platformLanguage;
        }
        $cities = $this->cityService->getCitiesWithCriteria($criteria, $limit, $offset);
        if ($cities) {
            $responseData['data'] = $cities;
        } else {

            return response()->json('', 204);
        }
    	return response()->json($responseData, 200);	
    }

    /**
     * Get All destinations
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAllDestinations(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->cityService->setRequestObject($requestedObject);
        }
        $cities = $this->cityService->getAllCities();
        $data["data"] = $cities;
        if ($cities) {
            return response()->json($data, 200);
        } else {
            return response()->json($data, 204);
        }

    }
}