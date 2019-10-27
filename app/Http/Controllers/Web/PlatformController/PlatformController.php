<?php

namespace App\Http\Controllers\Web\PlatformController;

use App\Http\Controllers\Abstracts\AbstractWebController;
use App\Utility\PreDefinedPaths;
use Dev\Domain\Service\PlatformService\PlatformService;
use Illuminate\Http\Request;

/**
 * PlatformController Class responsible for platforms actions
 * @package App\Http\Controllers\Web\PlatformController
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class PlatformController extends AbstractWebController
{
    /**
     * @var PlatformService $platformService
     */
    private $platformService;

    /**
     * @var string $platformJSONFilePath platform json file path
     */
    private $platformJSONFilePath;

    /**
     * PlatformController constructor.
     * @param PlatformService $platformService instance from PlatformService
     * @param Request $request instance from Request
     */
    public function __construct(Request $request, PlatformService $platformService)
    {
        parent::__construct($request);
        $this->platformService = $platformService;
        $this->platformJSONFilePath = PreDefinedPaths::getPlatformJSONFilePath();
    }

    /**
     * Generate available platforms json file
     * @return string
     */
    public function generatePlatformsJSONFile()
    {
        $platforms = $this->platformService->getPlatformsWithCriteria();
        $platformsCodes = [];
        foreach ($platforms as $platform) {
            $platformsCodes[] = $platform->code;
        }
        $jsonFile = fopen($this->platformJSONFilePath, 'w');
        fwrite($jsonFile, json_encode($platformsCodes, JSON_FORCE_OBJECT));
        fclose($jsonFile);
        return "Platforms JSON file has been created";
    }
}