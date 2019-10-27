<?php

namespace App\Utility;

/**
 * PreDefinedPaths Class contains constants of predefined paths
 * @package App\Utility
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class PreDefinedPaths
{
    /**
     * @var string UploadPaths::TRIP_IMAGES_PATH indicate upload path of trips images
     */
    const PLATFORM_JSON_FILE_PATH = "/dev/Application/platforms.json";

    /**
     * Return generated available platforms json file path
     * @return string
     */
    public static function getPlatformJSONFilePath()
    {
        $appRootPath = base_path();
        return $appRootPath . self::PLATFORM_JSON_FILE_PATH;
    }
}