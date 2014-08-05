<?php

namespace duncan3dc\Helpers;

class Image
{


    public static function getExtension($path, $extensions = null)
    {
        $format = exif_imagetype($path);

        return static::getFormatExtension($format, $extensions);
    }


    public static function getFormatExtension($format, $extensions = null)
    {
        switch ($format) {

            case "image/jpg":
            case "image/jpeg":
            case "image/pjpeg":
            case IMAGETYPE_JPEG:
                $ext = "jpg";
                break;

            case "image/gif":
            case IMAGETYPE_GIF:
                $ext = "gif";
                break;

            case "image/png":
            case IMAGETYPE_PNG:
                $ext = "png";
                break;

            default:
                return false;
        }

        if ($extensions) {
            $extensions = Helper::toArray($extensions);
            if (!in_array($ext, $extensions)) {
                return false;
            }
        }

        return $ext;
    }


    public static function getDate($path)
    {
        if ($path[0] != "/") {
            $path = Env::path($path);
        }

        if (!$exif = exif_read_data($path)) {
            return false;
        }

        if (!$date = strtotime($exif["DateTime"])) {
            return false;
        }

        return $date;
    }


    public static function resize($options = null)
    {
        $options = Helper::getOptions($options, [
            "fromPath"  =>  false,
            "toPath"    =>  false,
            "maxWidth"  =>  false,
            "maxHeight" =>  false,
        ]);

        if (!$fromPath = trim($options["fromPath"])) {
            return false;
        }
        if (!$toPath = trim($options["toPath"])) {
            return false;
        }
        $maxWidth = round($options["maxWidth"]);
        $maxHeight = round($options["maxHeight"]);

        list($width, $height, $format) = getimagesize($fromPath);

        if ($width < 1 || $height < 1) {
            copy($fromPath, $toPath);
            return false;
        }

        if ($maxWidth < 1 && $maxHeight < 1) {
            copy($fromPath, $toPath);
            return false;
        }

        if ($width < $maxWidth && $height < $maxHeight) {
            copy($fromPath, $toPath);
            return false;
        }

        $newWidth = $width;
        $newHeight = $height;

        if ($maxHeight && $newHeight > $maxHeight) {
            $ratio = $newWidth / $newHeight;
            $newHeight = $maxHeight;
            $newWidth = $newHeight * $ratio;
        }

        if ($maxWidth && $newWidth > $maxWidth) {
            $ratio = $newHeight / $newWidth;
            $newWidth = $maxWidth;
            $newHeight = $newWidth * $ratio;
        }

        if (!$image = static::create($fromPath, $format)) {
            return false;
        }
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($format != IMAGETYPE_JPEG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $background = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagecolortransparent($newImage, $background);
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        static::save($newImage, $toPath, $format);

        imagedestroy($image);
        imagedestroy($newImage);

        return true;
    }


    /**
     * Automatically resize and create images on the fly
     * If the image already exists then it's path is just passed back
     * A max width/height can be specified to resize the image to
     */
    public static function img($options = null)
    {
        $options = Helper::getOptions($options, [
            "path"      =>  "images/img",
            "basename"  =>  false,
            "filename"  =>  false,
            "width"     =>  false,
            "height"    =>  false,
        ]);

        $path = $options["path"];
        if ($path[0] == "/") {
            $fullpath = $path;
        } else {
            $fullpath = Env::path($path);
        }

        if ($basename = $options["basename"]) {
            $original = $fullpath . "/original/" . $basename;
            if (file_exists($original)) {
                if ($ext = static::getExtension($original)) {
                    $filename = $basename . "." . $ext;
                    copy($original, $fullpath . "/original/" . $filename);
                }
            }
        } else {
            $filename = $options["filename"];
        }

        if (!$filename) {
            return false;
        }

        $original = $fullpath . "/original/" . $filename;
        if (!file_exists($original)) {
            return false;
        }

        $w = $options["width"];
        $h = $options["height"];

        if (!$w && !$h) {
            return $path . "/original/" . $filename;
        }

        if ($w && $h) {
            $dir = "max" . $w . "x" . $h;
        } elseif ($w) {
            $dir = "width" . $w;
        } elseif ($h) {
            $dir = "height" . $h;
        }

        $fullpath .= "/" . $dir;
        $newfile = $fullpath . "/" . $filename;
        $newpath = $path . "/" . $dir . "/" . $filename;

        if (file_exists($newfile)) {
            return $newpath;
        }

        if (!is_dir($fullpath)) {
            mkdir($fullpath, 0777, true);
        }

        static::resize([
            "fromPath"  =>  $original,
            "toPath"    =>  $newfile,
            "maxWidth"  =>  $w,
            "maxHeight" =>  $h,
        ]);

        return $newpath;
    }


    public static function create($path, $format)
    {
        switch ($format) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($path);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($path);
        }

        return false;
    }


    public static function save($image, $path, $format)
    {
        switch ($format) {
            case IMAGETYPE_JPEG:
                return imagejpeg($image, $path, 100);
            case IMAGETYPE_PNG:
                return imagepng($image, $path, 9);
            case IMAGETYPE_GIF:
                return imagegif($image, $path);
        }

        return false;
    }


    public static function rotate($path, $rotate = null)
    {
        if ($rotate === false) {
            $exif = exif_read_data($path);
            switch ($exif["Orientation"]) {
                case 3:
                    $rotate = 180;
                    break;
                case 6:
                    $rotate = 90;
                    break;
                case 8:
                    $rotate = -90;
                    break;
            }
        }
        if (!$rotate) {
            return false;
        }

        $format = getimagesize($path)[2];

        if (!$image = static::create($path, $format)) {
            return false;
        }

        $rotate = imagerotate($image, $rotate * -1, 0);

        static::save($rotate, $path, $format);

        return true;
    }
}
