<?php

namespace duncan3dc\Helpers;

class Json
{

    public static function convert($data, $options = null)
    {

        $options = Helper::getOptions($options, [
            "cleanup"   =>  false,
        ]);

        # If the data is an array the assume we are encoding it
        if (is_array($data)) {
            if ($options["cleanup"]) {
                $data = Helper::cleanupArray($data);
            }
            return static::encode($data);

        # If the data isn't an array then assume we decoding it
        } else {
            $array = static::decode($data);
            if ($options["cleanup"]) {
                $array = Helper::cleanupArray($array);
            }
            return $array;
        }
    }


    public static function encode($data)
    {
        if (count($data) < 1) {
            return "";
        }

        $json = json_encode($data);

        static::checkLastError();

        return $json;
    }


    public static function decode($json)
    {
        if (!$json) {
            return [];
        }

        $data = json_decode($json, true);

        static::checkLastError();

        return $data;
    }


    public static function checkLastError()
    {
        $error = json_last_error();

        if ($error == JSON_ERROR_NONE) {
            return;
        }

        $message = json_last_error_msg();

        throw new \Exception("JSON Error: " . $message, $error);
    }


    public static function encodeToFile($path, $data)
    {
        $json = static::encode($data);

        # Ensure the directory exists
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return file_put_contents($path, $json);
    }


    public static function decodeFromFile($path)
    {
        if (!is_file($path)) {
            throw new \Exception("File does not exist (" . $path . ")");
        }

        $json = file_get_contents($path);

        return static::decode($json);
    }
}
