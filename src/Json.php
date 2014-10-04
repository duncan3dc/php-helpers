<?php

namespace duncan3dc\Helpers;

class Json
{

    /**
     * Convert the passed variable between array and json (depending on the type passed).
     *
     * $options:
     * - bool "cleanup" Whether values should be trimmed and falsy values removed (default: false)
     *
     * @param array|string The data to convert
     * @param array An array of options (see above)
     *
     * @return string|array
     */
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


    /**
     * Convert an array to a JSON string.
     *
     * @param array The data to encode
     *
     * @return string
     */
    public static function encode($data)
    {
        if (count($data) < 1) {
            return "";
        }

        $json = json_encode($data);

        static::checkLastError();

        return $json;
    }


    /**
     * Convert a JSON string to an array.
     *
     * @param array The data to decode
     *
     * @return array
     */
    public static function decode($json)
    {
        if (!$json) {
            return [];
        }

        $data = json_decode($json, true);

        static::checkLastError();

        return $data;
    }


    /**
     * Check if the last json operation returned an error and convert it to an exception.
     * Designed as an internal method called after any json operation,
     * but there's no reason it can't be called after a straight call to the php json_* functions.
     *
     * @return void
     */
    public static function checkLastError()
    {
        $error = json_last_error();

        if ($error == JSON_ERROR_NONE) {
            return;
        }

        $message = json_last_error_msg();

        throw new \Exception("JSON Error: " . $message, $error);
    }


    /**
     * Convert an array to a JSON string, and then write it to a file.
     * Attempts to create the directory if it does not exist.
     *
     * @param string The path to the file to write
     * @param array The data to decode
     *
     * @return void
     */
    public static function encodeToFile($path, $data)
    {
        $json = static::encode($data);

        # Ensure the directory exists
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        if (!file_put_contents($path, $json)) {
            throw new \Exception("Failed to write the file (" . $path . ")");
        }
    }


    /**
     * Read a JSON string from a file and convert it to an array.
     *
     * @param string The path of the file to read
     *
     * @return array
     */
    public static function decodeFromFile($path)
    {
        if (!is_file($path)) {
            throw new \Exception("File does not exist (" . $path . ")");
        }

        $json = file_get_contents($path);

        if ($json === false) {
            throw new \Exception("Failed to read the file (" . $path . ")");
        }

        return static::decode($json);
    }
}
