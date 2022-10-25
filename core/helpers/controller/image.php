<?php

namespace core\helpers\controller;

/**
 * Bunch of global useful image functions
 *
 * @author Dani Gilabert
 * 
 */
class image
{
    
    public static function getImageProperties($image_path)
    {
        if (!file_exists($image_path))
        {
            return null;
        }
        
        $imagesize = getimagesize($image_path);
        $pathinfo = pathinfo($image_path);
        $filesize = filesize($image_path);

        $ret = new \stdClass();
        $ret->path = $image_path;
        $ret->dirname = $pathinfo['dirname'];
        $ret->extension = $pathinfo['extension'];
        $ret->basename = $pathinfo['basename'];
        $ret->filename = $pathinfo['filename'];
        $ret->width = $imagesize[0];
        $ret->height = $imagesize[1];
        $ret->type = $imagesize[2];
        $ret->attr = $imagesize[3];
        $ret->filesize = round(($filesize/1024), 3). ' KB';
        $ret->bytes = $filesize;
        $ret->filedate = date("F d Y H:i:s", filemtime($image_path));
        
        return $ret;        
    }
    
    public static function createImage($src_path, $dst_path, $new_witdh, $new_height, $quality = 100, $type = IMAGETYPE_JPEG)
    {
        switch ( $type ){
            case IMAGETYPE_JPEG:
                $original = imagecreatefromjpeg( $src_path );
                break;
            case IMAGETYPE_PNG:
                $original = imagecreatefrompng( $src_path );
                break;
            case IMAGETYPE_GIF:
                $original = imagecreatefromgif( $src_path );
        }
        
        if ($original === false)
        {
            return false;
        }
        
        $img = imagecreatetruecolor($new_witdh, $new_height);
        if ($img === false)
        {
            return false;
        }
        
        if ($type === IMAGETYPE_PNG)
        {
            // Set background color to white
            $white = imagecolorallocate($img, 255, 255, 255);
            imagefilledrectangle($img, 0, 0, $new_witdh, $new_height, $white);            
        }
        
        $width = imagesx($original);
        $height = imagesy($original);

        imagecopyresampled($img, $original, 0, 0, 0, 0, $new_witdh, $new_height, $width, $height);
        
        // Output
        switch ( $type ){
            case IMAGETYPE_JPEG:
                $ret = imagejpeg($img, $dst_path, $quality);
                break;
            case IMAGETYPE_PNG:
                $ret = imagepng($img, $dst_path);
                break;
            case IMAGETYPE_GIF:
                $ret = imagegif($img, $dst_path);
        }
        
        imagedestroy($img);
        
        return $ret;
    }    
    
    public static function addWatermark($img_path, $watermark_path, $type = IMAGETYPE_JPEG)
    {
        // Load the watermark and the photo to apply the watermark to
        switch ( $type ){
            case IMAGETYPE_JPEG:
                $img = imagecreatefromjpeg( $img_path );
                break;
            case IMAGETYPE_PNG:
                $img = imagecreatefrompng( $img_path );
                break;
            case IMAGETYPE_GIF:
                $img = imagecreatefromgif( $img_path );
        }
        if ($img === false)
        {
            return false;
        }
        $img_w = imagesx($img);
        $img_h = imagesy($img);
        
        $original_watermark = imagecreatefrompng($watermark_path);
        if ($original_watermark === false)
        {
            return false;
        }
        
        $original_wtrmrk_w = imagesx($original_watermark);
        $original_wtrmrk_h = imagesy($original_watermark);
        
        // Resize watermark as much big as original img
        $watermark = imagecreatetruecolor($img_w, $img_h);
        
        /*
        // Fill with alpha background
        $transparent = imagecolorallocatealpha($watermark, 255, 255, 255, 127);
        imagefilledrectangle($watermark, 0, 0, $original_wtrmrk_w, $original_wtrmrk_h, $transparent);
        // Convert to palette-based with no dithering and 255 colors with alpha        
        imagealphablending($watermark, false);
        imagesavealpha($watermark, true);
        //imagetruecolortopalette($watermark, false, 255);
        */
        
        /*
        // Fill with alpha background
        $alphabg = imagecolorallocatealpha($watermark, 0, 0, 0, 127);
        imagecolortransparent($watermark, $alphabg);
        imagefill($watermark, 0, 0, $alphabg);
        // Convert to palette-based with no dithering and 255 colors with alpha
        imagetruecolortopalette($im, false, 255);
        imagesavealpha($im, true);        
        */
        
        // Fill with alpha background
        $alphabg = imagecolorallocatealpha($watermark, 0, 0, 0, 127);
        imagecolortransparent($watermark, $alphabg);
        imagefill($watermark, 0, 0, $alphabg);
        //imagetruecolortopalette($watermark, false, 255);       
        imagesavealpha($watermark, true);
        
        // Resize watermark!!
        imagecopyresampled($watermark, $original_watermark, 0, 0, 0, 0, $img_w, $img_h, $original_wtrmrk_w, $original_wtrmrk_h);
        
        /*
        // Test
        $ret = imagepng($watermark, '/tmp/zzz4.png');
        imagedestroy($watermark);
        return $ret;
        */
        
        // Merge image with watermark
        //imagecopymerge($img, $watermark, 0, 0, 0, 0, $img_w, $img_h, 80);
        imagecopy($img, $watermark, 0, 0, 0, 0, $img_w, $img_h);
        
        // Output
        switch ( $type ){
            case IMAGETYPE_JPEG:
                $ret = imagejpeg($img, $img_path, 100);
                break;
            case IMAGETYPE_PNG:
                $ret = imagepng($img, $img_path);
                break;
            case IMAGETYPE_GIF:
                $ret = imagegif($img, $img_path);
        }
        
        imagedestroy($img);
        imagedestroy($watermark);
    
        return $ret;
    }
    
}