<?php

namespace App\Helpers;

use Intervention\Image\ImageManagerStatic as Image;

class Helper
{
    
    /**
     *
     * @param  obnject file  $imgobj
     * @return image name after uploaded
     */
    public static function uploadImage($img_obj)
    {
        if ($img_obj) {
            $dest_path    = public_path('/images');
            $img_with_ext = $img_obj->getClientOriginalName();
            $img_name     = pathinfo($img_with_ext, PATHINFO_FILENAME);
            $img_ext      = '.' . pathinfo($img_with_ext, PATHINFO_EXTENSION);
            $i            = 1;
            $img_ori      = $img_name;
            while (file_exists($dest_path . '/' . $img_name . $img_ext)) {
                $img_name   = (string) $img_ori . '-' . $i  ;
                $img_with_ext = $img_name . $img_ext;
                $i++;
            }
            $img_obj->move($dest_path, $img_with_ext);
            return $img_with_ext;
        }
        return false;
    }

    public static function thumb($img, $dimensions)
    {
        $dest_path  = public_path('/images');
        $img_name   = pathinfo($img, PATHINFO_FILENAME);
        $img_ext    = '.' . pathinfo($img, PATHINFO_EXTENSION);
        $img_x      = $dimensions['suffix'];
        $img_w      = $dimensions['width'];
        $img_h      = $dimensions['height'];
        $img_path   = $dest_path . '/' . $img;
        $thumb_name = $img_name . $img_x . $img_ext ;
        try {
            $thumb_obj  = Image::make($img_path)->resize($img_w, $img_h, function ($constraint) {
                $constraint->aspectRatio();
            });
            $thumb_obj->save($dest_path . '/' .$thumb_name);
        } catch (Exception $e) {
            //log message here
        }
    }

    public static function imageStyleUrl($style, $image)
    {
        $images_path = url('public/images');
        $img_name    = pathinfo($image, PATHINFO_FILENAME);
        $img_ext     = '.' . pathinfo($image, PATHINFO_EXTENSION);
        $img_sizes   = \Config::get('app.image_sizes');
        if (isset($img_sizes[$style])) {
            $img_suffix  = $img_sizes[$style]['suffix'];
            return  $images_path.'/'.$img_name.$img_suffix.$img_ext;
        } else {
            return  $images_path.'/'.$image;
        }
    }

    public static function removeImages($img_full_name)
    {
        $dest_path = public_path('/images/');
        $img_sizes = \Config::get('app.image_sizes');
        $img_name  = pathinfo($img_full_name, PATHINFO_FILENAME);
        $img_ext   = strrchr($img_full_name, "."); // .jpg
        if ($img_sizes && $img_name  && $img_ext) {
            @unlink($dest_path . $img_full_name);
            foreach ($img_sizes as $size) {
                $path_to_img = $dest_path . $img_name . $size['suffix'] . $img_ext;
                @unlink($path_to_img);
            }
        }
        //return $img_name;
    }
}

