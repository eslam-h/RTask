<?php

namespace App\Utility;

/**
 * UploadPaths Class contains upload paths constants
 * @package App\Utility
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class UploadPaths
{
    /**
     * @var string UploadPaths::TRIP_IMAGES_PATH indicate upload path of trips images
     */
    const TRIP_IMAGES_PATH = "trips/images";

    /**
     * @var string UploadPaths::ACTIVITY_IMAGES_PATH indicate upload path of activities icons
     */
    const ACTIVITY_ICONS_PATH = "activities/icons";

    /**
     * @var string UploadPaths::ACTIVITY_IMAGES_PATH indicate upload path of activities icons
     */
    const ACTIVITY_IMAGES_PATH = "activities/images";

    /**
     * @var string UploadPaths::CITY_IMAGES_PATH indicate upload path of cities images
     */
    const CITY_IMAGES_PATH = "cities/images";

    /**
     * @var string UploadPaths::TEMP_TRIP_IMAGE_CROP_PATH indicate temp upload path of trip crop images
     */
    const TEMP_TRIP_IMAGE_CROP_PATH = "trips/temp";

    /**
     * @var string UploadPaths::TRIP_IMAGES_CROP_PATH indicate upload path of trips images crop {trip-id} is placeholder
     */
    const TRIP_IMAGES_CROP_PATH = "trips/images/{trip-id}/crop";

    /**
     * @var string UploadPaths::USER_PROFILE_IMAGE_PATH indicate upload path of user profile images
     */
    const USER_PROFILE_IMAGE_PATH = "users/images";
}
