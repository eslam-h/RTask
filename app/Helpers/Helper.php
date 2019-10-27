<?php

namespace App\Helpers;

use Storage;
use Intervention\Image\ImageManagerStatic as Image;

class Helper
{
	public static function resizeImage($imgPath, $style)
	{
		$imgStyles   = \Config::get('imagestyles');
		$imgPathInfo = pathinfo($imgPath);
		$imgStyleDir = $imgPathInfo['dirname'].'/'.$style;
		$imgWidth    = $imgStyles[$style]['width'];
		$imgHeight   = $imgStyles[$style]['height'];
        if(!Storage::disk('local')->exists($imgStyleDir))
        {
            Storage::makeDirectory( $imgStyleDir ); //creates directory
        }
        try {
            $imgObj  = Image::make('cdn/'.$imgPath)->fit($imgWidth, $imgHeight);
            $imgObj->save('cdn/' . $imgStyleDir . '/' .$imgPathInfo['basename']);
        } catch (Exception $e) {
           // echo $e->getMessage();
        }
	}

	public static function resizeImageAll($imgPath)
	{
		$imgStyles = \Config::get('imagestyles');
		if ($imgStyles) {
			foreach ($imgStyles as $k => $style) {
				self::resizeImage($imgPath, $k);
			}
		}
	}

	public static function imageSizeUrl($imgPath, $style)
	{
		$imgPathInfo = pathinfo($imgPath);
		$imgStylefile = $imgPathInfo['dirname'].'/'.$style.'/'.$imgPathInfo['basename'];
		$exists = Storage::exists($imgStylefile);
		if ($exists) {
			//return Storage::url($imgStylefile);
			return asset('cdn/'.$imgStylefile);
		} 
	}

	public static function imageSizesUrl($imgPath)
	{
		$paths     = [];
		$imgStyles = \Config::get('imagestyles');
		if ($imgStyles) {
			foreach ($imgStyles as $k => $v) {
				$paths[$k] = self::imageSizeUrl($imgPath, $k);
			}
		}
		return $paths;
	}
}