<?php

namespace App\Http\Controllers\Api\CityController;

use App\Http\Controllers\Abstracts\AbstractController;
use Dev\Domain\Service\CityService\CityService;
use Dev\Domain\Service\CityService\PopularCityService;
use Illuminate\Http\Request;
use Validator;


/**
 * PopularCityController Class for returning app city list
 * @package App\Http\Controllers\Api\PopularCityController
 * @author M.El-Wakeel <m.elwakeel@shiftebusiness.com>
 */
class PopularCityController extends AbstractController
{

    /**
     * @var CityService $cityService instance from city service
     */
    private $cityService;


    /**
     * @var PopularCityService $cityService instance from city service
     */
    private $popularCityService;

    /**
     * @var int $limit (number of items per page)
     */
    private $limit = 15;

    /**
     * CityController constructor.
     * @param CityService $cityService
     * @param Request $request
     * @param PopularCityService $popularCityService
     */
	public function __construct(Request $request, CityService $cityService, PopularCityService $popularCityService)
    {
        parent::__construct($request);
    	$this->cityService        = $cityService;
        $this->popularCityService = $popularCityService;
    }

    public function popularCities(Request $request)
    {
        $requestedObject = is_array($request->get("q")) ? $request->get("q") : null;
        if ($requestedObject) {
            $this->cityService->setRequestObject($requestedObject);
        }
        $responseData = [];
        $popularCities = $this->popularCityService->getPopularCities();
        if ($popularCities) {
            $responseData['data'] = $popularCities;
        } else {

            return response()->json('', 204);
        }
        return response()->json($responseData, 200);
    }

}