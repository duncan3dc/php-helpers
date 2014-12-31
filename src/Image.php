<?php

namespace duncan3dc\Helpers;

class Image
{

    /**
     * Get the extension of image file from disk.
     * Only supports png/jpg/gif.
     * Optionally pass an acceptable extension (or array of extensions).
     *
     * @param string $path The fully qualified path to the file
     * @param string|array $extensions Acceptable extensions
     *
     * @return string|null
     */
    public static function getExtension($path, $extensions = null)
    {
        $format = exif_imagetype($path);

        return static::getFormatExtension($format, $extensions);
    }


    /**
     * Get the extension of a type identifier (either a imagetype constant or a mimetype).
     * Only supports png/jpg/gif.
     * Optionally pass an acceptable extension (or array of extensions).
     *
     * @param int|string $format Either an imagetype constant or a mimetype string
     * @param string|array $extensions Acceptable extensions
     *
     * @return string|null
     */
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
                return;
        }

        if ($extensions) {
            $extensions = Helper::toArray($extensions);
            if (!in_array($ext, $extensions)) {
                return;
            }
        }

        return $ext;
    }


    /**
     * Get the date/time an image was taken (using exif data).
     *
     * @param string $path The path to the image file, if it starts with a forward slash then an absolute path is assumed, otherwise it is relative to the document root
     *
     * @return int|null Unix timestamp
     */
    public static function getDate($path)
    {
        if ($path[0] != "/") {
            $path = Env::path($path, Env::PATH_DOCUMENT_ROOT);
        }

        if (!$exif = exif_read_data($path)) {
            return;
        }

        if (empty($exif["DateTime"])) {
            return;
        }

        if (!$date = strtotime($exif["DateTime"])) {
            return;
        }

        return $date;
    }


    /**
     * Resize an image, maintaining the same aspect ratio.
     * If the image is already an appropriate size then it is just copied.
     *
     * $options:
     * - string "fromPath" The path of the current image file
     * - string "toPath" The path to save the resized image to
     * - int "width" The maximum width that the image can be
     * - int "height" The maximum height that the image can be
     *
     * @param array $options An array of options (see above)
     *
     * @return null
     */
    public static function resize($options = null)
    {
        $options = Helper::getOptions($options, [
            "fromPath"  =>  null,
            "toPath"    =>  null,
            "width"     =>  null,
            "height"    =>  null,
        ]);

        if (!$fromPath = trim($options["fromPath"])) {
            throw new \Exception("No from path specified to read from");
        }
        if (!$toPath = trim($options["toPath"])) {
            throw new \Exception("No to path specified to save to");
        }
        $maxWidth = round($options["width"]);
        $maxHeight = round($options["height"]);

        list($width, $height, $format) = getimagesize($fromPath);

        if ($width < 1 || $height < 1) {
            copy($fromPath, $toPath);
            return;
        }

        if ($maxWidth < 1 && $maxHeight < 1) {
            copy($fromPath, $toPath);
            return;
        }

        if ($width < $maxWidth && $height < $maxHeight) {
            copy($fromPath, $toPath);
            return;
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

        $image = static::create($fromPath, $format);
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
    }


    /**
     * Automatically resize and create images on the fly.
     * If the image already exists then it's path is just passed back.
     *
     * $options:
     * - string "path" The path that the images reside in (default: "images/img")
     * - string "basename" The filename, without extension, of the image, the extension will be automatically established and appended
     * - string "filename" The filename, including extension, of the image. Either this or "basename" must be set
     * - int "width" The maximum width that the image can be, if the image is wider it will be resized (maintaining the same aspect ratio)
     * - int "height" The maximum height that the image can be, if the image is taller it will be resized (maintaining the same aspect ratio)
     *
     * @param array $options An array of options (see above)
     *
     * @return string
     */
    public static function img($options = null)
    {
        $options = Helper::getOptions($options, [
            "path"      =>  "images/img",
            "basename"  =>  null,
            "filename"  =>  null,
            "width"     =>  null,
            "height"    =>  null,
        ]);

        $path = $options["path"];
        if ($path[0] == "/") {
            $fullpath = $path;
        } else {
            $fullpath = Env::path($path, Env::PATH_DOCUMENT_ROOT);
        }

        $filename = $options["filename"];
        if ($basename = $options["basename"]) {
            $original = $fullpath . "/original/" . $basename;
            if (file_exists($original)) {
                if ($ext = static::getExtension($original)) {
                    $filename = $basename . "." . $ext;
                    copy($original, $fullpath . "/original/" . $filename);
                }
            }
        }

        if (!$filename) {
            throw new \Exception("No image filename provided to use");
        }

        $original = $fullpath . "/original/" . $filename;
        if (!file_exists($original)) {
            throw new \Exception("Original image file does not exist (" . $original . ")");
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
            "width"     =>  $w,
            "height"    =>  $h,
        ]);

        return $newpath;
    }


    /**
     * Create an image resource from an image on disk.
     *
     * @param string $path The path to load the image from
     * @param int $format Image type constant (http://php.net/manual/en/image.constants.php)
     *
     * @return resource
     */
    public static function create($path, $format)
    {
        switch ($format) {
            case IMAGETYPE_JPEG:
                $result = imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG:
                $result = imagecreatefrompng($path);
                break;
            case IMAGETYPE_GIF:
                $result = imagecreatefromgif($path);
                break;
        }

        if (!$result) {
            throw new \Exception("Failing to create image (" . $path . ")");
        }

        return $result;
    }


    /**
     * Save an image resource to the disk.
     *
     * @param resource $image The image resource to save
     * @param string $path The path to save the image too
     * @param int $format Image type constant (http://php.net/manual/en/image.constants.php)
     *
     * @return void
     */
    public static function save($image, $path, $format)
    {
        switch ($format) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($image, $path, 100);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($image, $path, 9);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($image, $path);
                break;
        }

        if (!$result) {
            throw new \Exception("Failed to save image (" . $path . ")");
        }
    }


    /**
     * Rotate an image and save it.
     * If no angle to rotate is passed then we attempt to read it from exif data.
     *
     * @param string $path The path of the image to work with, and overwrite
     * @param int $rotate A specific rotation angle to apply (clockwise)
     *
     * @return void
     */
    public static function rotate($path, $rotate = null)
    {
        if ($rotate === null) {
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
            return;
        }

        $format = getimagesize($path)[2];

        $image = static::create($path, $format);

        $rotate = imagerotate($image, $rotate * -1, 0);

        static::save($rotate, $path, $format);
    }
}
