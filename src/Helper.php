<?php

namespace duncan3dc\Helpers;

use duncan3dc\Serial\ArrayObject as SerialObject;

/**
 * A bunch of random helper functions.
 */
class Helper
{

    /**
     * Simulate named arguments using associative arrays.
     * Basically just merge the two arrays, giving user specified options the preference.
     * Also ensures that each paramater in the user array is valid and throws an exception if an unknown element is found.
     *
     * @param array $userSpecified The array of options passed to the function call
     * @param array $defaults The default options to be used
     *
     * @return array
     */
    public static function getOptions($userSpecified, $defaults)
    {
        $options = static::getAnyOptions($userSpecified, $defaults);

        foreach ($options as $key => $null) {
            if (array_key_exists($key, $defaults)) {
                continue;
            }
            throw new \InvalidArgumentException("Unknown parameter (" . $key . ")");
        }

        return $options;
    }


    /**
     * This is a safe version of the getOptions() method.
     * It allows any custom option key in the userSpecified array.
     *
     * @param array $userSpecified The array of options passed to the function call
     * @param array $defaults The default options to be used
     *
     * @return array
     */
    public static function getAnyOptions($userSpecified, $defaults)
    {
        $options = $defaults;
        $userSpecified = static::toArray($userSpecified);

        foreach ($userSpecified as $key => $val) {
            $options[$key] = $val;
        }

        return $options;
    }


    /**
     * Ensure that the passed parameter is a string, or an array of strings.
     *
     * @param mixed $data The value to convert to a string
     *
     * @return string|string[]
     */
    public static function toString($data)
    {
        if (is_array($data)) {
            $newData = [];
            foreach ($data as $key => $val) {
                $key = (string)$key;
                $newData[$key] = static::toString($val);
            }
        } else {
            $newData = (string)$data;
        }

        return $newData;
    }


    /**
     * Ensure that the passed parameter is an array.
     * If it is a truthy value then make it the sole element of an array.
     *
     * @param mixed $value The value to convert to an array
     *
     * @return array
     */
    public static function toArray($value)
    {
        # If it's already an array then just pass it back
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof SerialObject) {
            return $value->asArray();
        }

        if ($value instanceof \ArrayObject) {
            return $value->getArrayCopy();
        }

        # If it's not an array then create a new array to be returned
        $array = [];

        # If a value was passed as a string/int then include it in the new array
        if ($value) {
            $array[] = $value;
        }

        return $array;
    }


    /**
     * Run each element value through trim() and remove any elements that are falsy.
     *
     * @param array $array The array to cleanup
     *
     * @return array
     */
    public static function cleanupArray($array)
    {
        $newArray = [];

        $array = static::toArray($array);

        foreach ($array as $key => $val) {

            if (is_array($val)) {
                $val = static::cleanupArray($val);
            } else {
                $val = trim($val);
                if (!$val) {
                    continue;
                }
            }

            $newArray[$key] = $val;
        }

        return $newArray;
    }


    /**
     * Extension to the standard date() function to handle many more formats.
     * Returns zero if any step of the parsing fails.
     *
     * @param string $format The format to apply to the date
     * @param string|int $date The date to parse
     * @param string|int $time The time to parse
     *
     * @return int|string
     */
    public static function date($format, $date, $time = null)
    {
        if (!$date = trim($date)) {
            return 0;
        }

        if (preg_match("/[a-z]/i", $date)) {
            return 0;
        }

        # Define some time handling code to be used in several places below
        $timeFunc = function($time = null) {

            $return = [12, 0, 0];

            if (!$time) {
                return $return;
            }

            if (preg_match("/[a-z]/i", $time)) {
                return $return;
            }

            # Human readable format (h:i:s)
            if (strpos($time, ":")) {
                return explode(":", $time);
            }

            # Sortable format (His)
            return [
                floor($time / 10000),
                floor(($time / 100) % 100),
                $time % 100,
            ];
        };

        # Human readable universal format (yyyy-mm-dd)
        if (preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/", $date, $matches)) {
            list($h, $i, $s) = $timeFunc($time);
            $date = mktime($h, $i, $s, $matches[2], $matches[3], $matches[1]);

        # Sql date format (yyyy-mm-dd hh:ii:ss) (with optional milliseconds)
        } elseif (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})[\s-]([0-9]{2})[:\.]([0-9]{2})[:\.]([0-9]{2})(\.[0-9]{6})?$/", $date, $matches)) {
            list($null, $y, $m, $d, $h, $i, $s) = $matches;
            $date = mktime($h, $i, $s, $m, $d, $y);

            list($null, $y, $m, $d, $h, $i, $s) = $matches;
            $date = mktime($h, $i, $s, $m, $d, $y);

        # Human readable format (d/m/y or d-m-y)
        } elseif (strpos($date, "/") || strpos($date, "-")) {
            $char = (strpos($date, "/")) ? "/" : "-";
            if (!$time && strpos($date, " ")) {
                list($date, $time) = explode(" ", $date);
            }
            list($d, $m, $y) = explode($char, $date);
            list($h, $i, $s) = $timeFunc($time);
            $date = mktime($h, $i, $s, $m, $d, $y);

        # Sortable format (YmdHi/YmdHis)
        } elseif ($date > 200000000000) {
            $y = substr($date, 0, 4);
            $m = substr($date, 4, 2);
            $d = substr($date, 6, 2);
            $h = substr($date, 8, 2);
            $i = substr($date, 10, 2);
            $s = substr($date, 12, 2);
            $date = mktime($h, $i, $s, $m, $d, $y);

        # Sortable format (Year and month only)
        } elseif ($date < 999999) {
            $y = floor($date / 100);
            $m = $date % 100;
            $date = mktime(12, 0, 0, $m, 1, $y);

        # IBM DB2 format (cymd with optional separate time)
        } elseif ($date < 9999999) {
            $y = floor($date / 10000) + 1900;
            $m = floor(($date / 100) % 100);
            $d = $date % 100;

            if (!$time && strpos($date, " ")) {
                list($date, $time) = explode(" ", $date);
            }
            list($h, $i, $s) = $timeFunc($time);

            $date = mktime($h, $i, $s, $m, $d, $y);

        # Sortable format (Ymd with optional separate time)
        } elseif ($date < 99999999) {
            $y = substr($date, 0, 4);
            $m = substr($date, 4, 2);
            $d = substr($date, 6, 2);

            if (!$time && strpos($date, " ")) {
                list($date, $time) = explode(" ", $date);
            }
            list($h, $i, $s) = $timeFunc($time);

            $date = mktime($h, $i, $s, $m, $d, $y);
        }

        $return = date($format, $date);

        # If the result looks like a number then return it as an int
        if (preg_match("/^[0-9]+$/", $return)) {
            # Don't attempt to cast a number out of the standard 32-bit int range
            if ($return < 2147483648) {
                $return = (int)$return;
            }
        }

        return $return;
    }


    /**
     * Compare two dates and return an integer representing their difference in days.
     * Returns null if any of the parsing fails.
     *
     * @param string|int $from The first date to parse
     * @param string|int $to The second date to parse
     *
     * @return int|null
     */
    public static function dateDiff($from, $to = null)
    {
        if (!$to) {
            $to = $from;
            $from = date("d/m/Y");
        }

        if (!$dateFrom = static::date("U", $from)) {
            return;
        }

        if (!$dateTo = static::date("U", $to)) {
            return;
        }

        $diff = $dateTo - $dateFrom;
        $days = round($diff / 86400);

        return $days;
    }


    /**
     * Append parameters on a url (adding a question mark if none is present).
     *
     * @param string $url The base url
     * @param array $params An array of parameters to append
     *
     * @return string
     */
    public static function url($url, $params = null)
    {
        if (!is_array($params) || count($params) < 1) {
            return $url;
        }

        $pos = strpos($url, "?");

        # If there is no question mark in the url then set this as the first parameter
        if ($pos === false) {
            $url .= "?";

        # If the question mark is the last character then no appending is required
        } elseif ($pos != (mb_strlen($url) - 1)) {

            # If the last character is not an ampersand then append one
            if (mb_substr($url, -1) != "&") {
                $url .= "&";
            }
        }

        $url .= http_build_query($params);

        return $url;
    }


    /**
     * Calculate the most reasonable divisor for a total.
     * Useful for repeating headers in a table with many rows.
     *
     * $options:
     * - int "min" The minimum number of rows to allow before breaking (default: 5)
     * - int "max" The maximum number of rows to allow before breaking (default: 10)
     *
     * @param int $rows The total number to calculate from (eg, total number of rows in a table)
     * @param array $options An array of options (see above)
     *
     * @return int
     */
    public static function getBestDivisor($rows, $options = null)
    {
        $options = static::getOptions($options, [
            "min"   =>  5,
            "max"   =>  10,
        ]);

        if ($rows <= $options["max"]) {
            return $rows;
        }

        $divisor = false;
        $divisorDiff = false;

        for ($i = $options["max"]; $i >= $options["min"]; $i--) {
            $remain = $rows % $i;

            # Calculate how close the remainder is to the postentional divisor
            $quality = $i - $remain;

            # If no divisor has been set yet then set it to this one, and record it's quality
            if (!$num) {
                $divisor = $i;
                $divisorQuality = $quality;
                continue;
            }

            # If the potentional divisor is a better match than the currently selected one then select it instead
            if ($quality < $divisorQuality) {
                $divisor = $i;
                $divisorQuality = $quality;
            }
        }

        return $divisor;
    }


    /**
     * Generate a password.
     *
     * $options:
     * - array "bad" Characters that should not be used as they are ambigious (default: [1, l, I, 5, S, 0, O, o])
     * - array "exclude" Other characters that should not be used (default: [])
     * - int "length" The length of the password that should be generated (default: 10)
     * - bool "lowercase" Include lowercase letters (default: true)
     * - bool "uppercase" Include uppercase letters (default: true)
     * - bool "numbers" Include numbers (default: true)
     * - bool "specialchars" Include special characters (default: true)
     *
     * @param array $options An array of options (see above)
     *
     * @return string
     */
    public static function createPassword($options = null)
    {
        $options = static::getOptions($options, [
            "bad"           =>  ["1", "l", "I", "5", "S", "0", "O", "o"],
            "exclude"       =>  [],
            "length"        =>  10,
            "lowercase"     =>  true,
            "uppercase"     =>  true,
            "numbers"       =>  true,
            "specialchars"  =>  true,
        ]);

        $password = "";

        if (!$options["lowercase"] && !$options["specialchars"] && !$options["numbers"] && !$options["uppercase"]) {
            return $password;
        }

        $exclude = array_merge($options["bad"], $options["exclude"]);

        # Keep adding characters until the password is at least as long as required
        while (mb_strlen($password) < $options["length"]) {

            # Add a few characters from each acceptable set

            if ($options["lowercase"]) {
                $max = rand(1, 3);
                for ($i = 0; $i < $max; ++$i) {
                    $password .= chr(rand(97, 122));
                }
            }

            if ($options["specialchars"]) {
                $max = rand(1, 3);
                for ($i = 0; $i < $max; ++$i) {
                    switch (rand(0, 3)) {
                        case 0:
                            $password .= chr(rand(33, 47));
                            break;
                        case 1:
                            $password .= chr(rand(58, 64));
                            break;
                        case 2:
                            $password .= chr(rand(91, 93));
                            break;
                        case 3:
                            $password .= chr(rand(123, 126));
                            break;
                    }
                }
            }

            if ($options["numbers"]) {
                $max = rand(1, 3);
                for ($i = 0; $i < $max; ++$i) {
                    $password .= chr(rand(48, 57));
                }
            }

            if ($options["uppercase"]) {
                $max = rand(1, 3);
                for ($i = 0; $i < $max; ++$i) {
                    $password .= chr(rand(65, 90));
                }
            }

            # Remove excluded characters
            $password = str_replace($exclude, "", $password);
        }

        # Reduce the length of the generated password to the required length
        $password = mb_substr($password, 0, $options["length"]);

        return $password;
    }


    /**
     * Check if a password conforms to the specified complexitiy rules.
     * If the password passes all tests then the function returns an empty array.
     * Otherwise it returns an array of all the checks that failed.
     *
     * $options:
     * - int "length" The minimum length the password must be (default: 8)
     * - int "unique" The number of unique characters the password must contain (default: 4)
     * - bool "lowercase" Whether the password must contain lowercase letters (default: true)
     * - bool "uppercase" Whether the password must contain uppercase letters (default: true)
     * - bool "alpha" Whether the password must contain letters (default: true)
     * - bool "numbers" Whether the password must contain numbers (default: true)
     *
     * @param string $password The password to check
     * @param array $options An array of options (see above)
     *
     * @return string[]
     */
    public static function checkPassword($password, $options = null)
    {
        $options = static::getOptions($options, [
            "length"    =>  8,
            "unique"    =>  4,
            "lowercase" =>  true,
            "uppercase" =>  true,
            "alpha"     =>  true,
            "numeric"   =>  true,
        ]);

        $problems = [];

        $len = mb_strlen($password);

        if ($len < $options["length"]) {
            $problems["length"] = "Passwords must be at least " . $options["length"] . " characters long";
        }

        $unique = [];
        for ($i = 0; $i < $len; ++$i) {
            $char = mb_substr($password, $i, 1);
            if (!in_array($char, $unique)) {
                $unique[] = $char;
            }
        }
        if (count($unique) < $options["unique"]) {
            $problems["unique"] = "Passwords must contain at least " . $options["unique"] . " unique characters";
        }

        if (!preg_match("/[a-z]/", $password)) {
            $problems["lowercase"] = "Passwords must contain at least 1 lowercase letter";
        }
        if (!preg_match("/[A-Z]/", $password)) {
            $problems["uppercase"] = "Passwords must contain at least 1 uppercase letter";
        }
        if (!preg_match("/[a-z]/i", $password)) {
            $problems["alpha"] = "Passwords must contain at least 1 letter";
        }
        if (!preg_match("/[0-9]/", $password)) {
            $problems["numeric"] = "Passwords must contain at least 1 number";
        }

        return $problems;
    }



    /**
     * Simple wrapper for curl.
     *
     * $options:
     * - string "url" The url to request
     * - array "headers" An array of key/value pairs of headers to send
     * - int "connect" CURLOPT_CONNECTTIMEOUT (default: 0)
     * - int "timeout" CURLOPT_TIMEOUT (default: 0)
     * - bool "follow" CURLOPT_FOLLOWLOCATION (default: true)
     * - bool "verifyssl" CURLOPT_SSL_VERIFYPEER (default: true)
     * - string "cookies" The file to use for both CURLOPT_COOKIEFILE and CURLOPT_COOKIEJAR
     * - bool "put" Set to try to send the $body parameter as the contents of a put request
     * - string "custom" CURLOPT_CUSTOMREQUEST
     * - bool "nobody" CURLOPT_NOBODY
     * - string "useragent" CURLOPT_USERAGENT
     * - bool "returnheaders" CURLOPT_HEADER (default: false)
     * - array "curlopts" Any extra curlopt constants (as the keys) and the values to use (as the values)
     *
     * @param string|array $options Can either be a url to use with the default options, or an array of options (see above)
     * @param string|array $body Content to send in the body of the request, if an array is passed it will be run through http_build_query()
     *
     * @return string|array If "returnheaders" is false then the body of the response is returned as a string, otherwise an array of data about the response is available
     */
    public static function curl($options, $body = null)
    {
        # If the options weren't passed as an array then it is just a simple url request
        if (!is_array($options)) {
            $options = ["url" => $options];
        }

        $options = Helper::getOptions($options, [
            "url"           =>  null,
            "headers"       =>  null,
            "connect"       =>  0,
            "timeout"       =>  0,
            "follow"        =>  true,
            "verifyssl"     =>  true,
            "cookies"       =>  null,
            "put"           =>  false,
            "custom"        =>  false,
            "nobody"        =>  false,
            "useragent"     =>  "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:35.0) Gecko/20100101 Firefox/35.0",
            "returnheaders" =>  false,
            "curlopts"      =>  [],
        ]);

        if (!$url = trim($options["url"])) {
            throw new \Exception("No url specified");
        }

        # If an array of post data has been passed then convert it into a query string
        if (is_array($body)) {
            $body = http_build_query($body);
        }

        $curlopts = [
            CURLOPT_URL             =>  $url,
            CURLOPT_RETURNTRANSFER  =>  true,
            CURLOPT_NOBODY          =>  $options["nobody"],
        ];

        if ($options["put"]) {
            $file = fopen("php://memory", "w");
            fwrite($file, $body);
            rewind($file);

            $curlopts[CURLOPT_PUT] = true;
            $curlopts[CURLOPT_INFILE] = $file;
            $curlopts[CURLOPT_INFILESIZE] = strlen($body);
        } elseif ($body) {
            $curlopts[CURLOPT_POST] = true;
            $curlopts[CURLOPT_POSTFIELDS] = $body;
        }

        if ($custom = $options["custom"]) {
            $curlopts[CURLOPT_CUSTOMREQUEST] = $custom;
        }

        if ($headers = $options["headers"]) {
            $header = [];
            foreach ($headers as $key => $val) {
                $header[] = $key . ": " . $val;
            }
            $curlopts[CURLOPT_HTTPHEADER] = $header;
        }

        $curlopts[CURLOPT_CONNECTTIMEOUT] = round($options["connect"]);
        $curlopts[CURLOPT_TIMEOUT] = round($options["timeout"]);

        if ($options["follow"]) {
            $curlopts[CURLOPT_FOLLOWLOCATION] = true;
        }

        if (!$options["verifyssl"]) {
            $curlopts[CURLOPT_SSL_VERIFYPEER] = false;
        }

        if ($cookies = $options["cookies"]) {
            $curlopts[CURLOPT_COOKIEFILE]   =   $cookies;
            $curlopts[CURLOPT_COOKIEJAR]    =   $cookies;
        }

        if ($useragent = $options["useragent"]) {
            $curlopts[CURLOPT_USERAGENT] = $useragent;
        }

        if ($options["returnheaders"]) {
            $curlopts[CURLOPT_HEADER] = true;
        }

        if (count($options["curlopts"]) > 0) {
            foreach ($options["curlopts"] as $key => $val) {
                $curlopts[$key] = $val;
            }
        }

        $curl = curl_init();

        curl_setopt_array($curl, $curlopts);

        $result = curl_exec($curl);

        $error = curl_error($curl);

        if ($options["returnheaders"]) {
            $info = curl_getinfo($curl);
        }

        curl_close($curl);

        if ($result === false) {
            throw new \Exception($error);
        }

        if ($options["returnheaders"]) {
            $header = substr($result, 0, $info["header_size"]);
            $lines = explode("\n", $header);
            $status = array_shift($lines);
            $headers = [];
            foreach ($lines as $line) {
                if (!trim($line)) {
                    continue;
                }
                $bits = explode(":", $line);
                $key = array_shift($bits);
                $headers[$key] = trim(implode(":", $bits));
            }
            $body = substr($result, $info["header_size"]);
            return [
                "status"    =>  $status,
                "headers"   =>  $headers,
                "body"      =>  $body,
                "response"  =>  $result,
            ];
        }

        return $result;
    }
}
