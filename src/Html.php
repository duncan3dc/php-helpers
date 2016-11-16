<?php

namespace duncan3dc\Helpers;

use duncan3dc\Serial\ArrayObject as SerialObject;
use duncan3dc\Serial\Yaml;

class Html
{

    /**
     * Format a number as price.
     *
     * $options:
     * - string "class" The css class to apply to the element if the number is negative (default: "red")
     * - string "currency" The currency code that the price is in (use Html::getCurrencies() to get supported currency codes)
     * - string "symbol" Instead of a currency a symbol can be provided directly (default: "£")
     * - string "symbolp" The same as symbol except it is added after the price, not before it
     * - int "decimalPlaces" The number of decimal places the price should show (default: 2)
     *
     * @param int|float $val The number to format
     * @param array $options An array of options (see above)
     *
     * @return string
     */
    public static function price($val, $options = null)
    {
        $options = Helper::getOptions($options, [
            "class"         =>  "red",
            "currency"      =>  null,
            "symbol"        =>  "£",
            "symbolp"       =>  "",
            "decimalPlaces" =>  2,
        ]);

        $val = round($val, $options["decimalPlaces"]);
        $val = number_format($val, $options["decimalPlaces"]);

        $return = "";

        if ($val < 0) {
            $return .= "<span ";
                if ($options["class"]) {
                    $return .= "class='" . $options["class"] . "' ";
                }
            $return .= ">";
        }

        if ($options["currency"]) {
            $currencies = static::getCurrencies(true);
            if ($currency = $currencies[$options["currency"]]) {
                $options["symbol"] = $currency["prefix"];
                $options["symbolp"] = $currency["suffix"];
            }
        }

        if ($options["symbol"]) {
            $return .= $options["symbol"];
        }

        $return .= $val;

        if ($options["symbolp"]) {
            $return .= " " . $options["symbolp"];
        }

        if ($val < 0) {
            $return .= "</span>";
        }

        return $return;
    }


    /**
     * Get the currencies supported by the Html class methods.
     *
     * @param boolean $symbols Whether to return a multi-dimensional array with the symbols of a currency, or just the currency codes and their names
     *
     * @return array Keyed by the currency code, value depends on the $symbols parameter
     */
    public static function getCurrencies($symbols = null)
    {
        $currencies = Cache::call("get-currencies", function() {
            $currencies = Yaml::decodeFromFile(__DIR__ . "/../data/currencies.yaml");

            if ($currencies instanceof SerialObject) {
                $currencies = $currencies->asArray();
            }

            return array_map(function($data) {
                if (!isset($data["prefix"])) {
                    $data["prefix"] = "";
                }
                if (!isset($data["suffix"])) {
                    $data["suffix"] = "";
                }
                return $data;
            }, $currencies);
        });

        if ($symbols) {
            return $currencies;
        }

        $return = [];
        foreach ($currencies as $key => $val) {
            $return[$key] = $val["title"];
        }

        return $return;
    }


    /**
     * Format a string in a human readable way.
     * Typically used for converting "safe" strings like "section_title" to user displayable strings like "Section Title"
     *
     * $options:
     * - string "underscores" Convert underscores to spaces (default: true)
     * - string "ucwords" Run the string through ucwords (default: true)
     *
     * @param string $key The string to format
     * @param array $options An array of options (see above)
     *
     * @return string
     */
    public static function formatKey($key, $options = null)
    {
        $options = Helper::getOptions($options, [
            "underscores"   =>  true,
            "ucwords"       =>  true,
        ]);

        if ($options["underscores"]) {
            $val = str_replace("_", "&nbsp;", $key);
        }

        if ($options["ucwords"]) {
            $val = ucwords($val);
        }

        return $val;
    }


    /**
     * Trim a string, and return an alternative if it is falsey.
     *
     * $options:
     * - string "alt" The string that should be returned if the input is falsey (default: "n/a")
     *
     * @param string $string The string to format
     * @param array $options An array of options (see above)
     *
     * @return string
     */
    public static function string($string, $options = null)
    {
        $options = Helper::getOptions($options, [
            "alt"       =>  "n/a",
        ]);

        $string = trim($string);

        if (!$string) {
            return $options["alt"];
        }

        return $string;
    }


    /**
     * Limit a string to a maximum length.
     *
     * $options:
     * - int "length" The maximum length that the string can be (default: 30)
     * - int "extra" The extra length that is acceptable, while aiming for the above length (default: 0)
     * - string "alt" The string that should be returned if the input is falsey (default: "n/a")
     * - bool "words" Set to true to never break in the middle of a word (default: true)
     * - string "suffix" The string that should be appended if the input has been shortened (default: "...")
     *
     * @param string $string The string to limit
     * @param array $options An array of options (see above)
     *
     * @return string
     */
    public static function stringLimit($string, $options = null)
    {
        $options = Helper::getOptions($options, [
            "length"    =>  30,
            "extra"     =>  0,
            "alt"       =>  "n/a",
            "words"     =>  true,
            "suffix"    =>  "...",
        ]);

        if (!$string = trim($string)) {
            return $options["alt"];
        }

        if ($options["words"]) {
            while (mb_strlen($string) > ($options["length"] + $options["extra"])) {
                $string = mb_substr($string, 0, $options["length"]);
                $string = trim($string);

                $words = explode(" ", $string);
                array_pop($words);
                $string = implode(" ", $words);

                $string .= $options["suffix"];
            }
        } else {
            $length = $options["length"] + $options["extra"] + mb_strlen($options["suffix"]);
            if (mb_strlen($string) > $length) {
                $string = mb_substr($string, 0, $length);
                $string .= $options["suffix"];
            }
        }

        return $string;
    }


    /**
     * Parse a date in the same way as Helper::date() except return a string on failure.
     *
     * $options:
     * - string "alt" The string that should be returned if Helper::date() returns 0 (default: "n/a")
     *
     * @param string $format The format to apply to the date
     * @param string|int $date The date to parse
     * @param string|int $time The time to parse
     * @param array $options An array of options (see above)
     *
     * @return string
     */
    public static function date($format, $date, $time = null, $options = null)
    {
        $options = Helper::getOptions($options, [
            "alt"   =>  "n/a",
        ]);

        $return = Helper::date($format, $date, $time);

        if (!$return) {
            return $options["alt"];
        }

        return $return;
    }


    /**
     * Convert a date into a textual string described the date/time relative to now.
     *
     * @param string|int $date The date to parse
     *
     * @return string
     */
    public static function textDate($date)
    {
        $date = Helper::date("U", $date);

        $diff = Helper::dateDiff($date, time());

        $text = "";

        # If the difference is less than 1 full day then claim it was today
        if ($diff < 1) {
            $text .= "Today";

        # If it's more than 1 day, but less then 2 then claim it was yesterday
        } elseif ($diff < 2) {
            $text .= "Yesterday";

        # If was over a month ago then display a date
        } elseif ($diff > 30) {
            $text .= date("D jS M", $date);
            $year = date("Y", $date);
            if ($year < date("Y")) {
                $text.= " " . $year;
            }

        # Otherwise display how many days ago it was
        } else {
            $text .= $diff . " days ago";
        }

        $text .= " at " . date("g:ia", $date);

        return $text;
    }


    public static function img($options)
    {
        if (!is_array($options)) {
            $options = array(
                "src"   =>  $options,
            );
        }

        $options = Helper::getOptions($options, [
            "id"        =>  "",
            "src"       =>  "",
            "default"   =>  "",
            "class"     =>  "",
            "alt"       =>  "",
            "title"     =>  "",
            "getSize"   =>  false,
        ]);

        $path = Env::getPath($options["src"]);

        /**
         * If a default image has been specifed then check if the image requested exists
         * If the image doesn't exist, then use the default image instead
         */
        if ($options["default"]) {
            if (!file_exists($path)) {
                $options["src"] = $options["default"];
            }
        }

        /**
         * If no alt text was specified then use any title text that may have been specified
         */
        if (!$options["alt"]) {
            $options["alt"] = $options["title"];
        }

        $img = "<img ";
            $img .= "src='" . $options["src"] . "' ";
            if ($options["id"]) {
                $img .= "id='" . $options["id"] . "' ";
            }
            if ($options["class"]) {
                $img .= "class='" . $options["class"] . "' ";
            }
            if ($options["alt"]) {
                $img .= "alt='" . Html::entities($options["alt"]) . "' ";
            }
            if ($options["title"]) {
                $img .= "title='" . Html::entities($options["title"]) . "' ";
            }
            if ($options["getSize"]) {
                list($width, $height) = getimagesize($path);
                if ($width > 0 && $height > 0) {
                    $img .= "style='width:" . $width . "px;height:" . $height . "px;' ";
                }
            }
        $img .= ">";

        return $img;
    }


    /**
     * Wrapper for htmlentities() with default options.
     * Identical to calling htmlentities($string, ENT_QUOTES, "UTF-8")
     *
     * @param string $string The string to convert the entities from
     *
     * @return string
     */
    public static function entities($string)
    {
        return htmlentities($string, ENT_QUOTES, "UTF-8");
    }


    /**
     * Take a number of seconds and convert it to the relevant units (seconds, minutes, hours, etc)
     *
     * @param int $seconds The number of seconds
     *
     * @return string
     */
    public static function time($seconds)
    {
        if ($seconds < 0) {
            return "n/a";
        }

        if ($seconds < 60) {
            return $seconds . " Seconds";
        }

        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        if ($minutes < 60) {
            if ($seconds >= 30) {
                $minutes++;
            }
            return $minutes . " Minutes";
        }

        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;

        if ($hours < 24) {
            if ($minutes >= 30) {
                $hours++;
            }
            return $hours . " Hours";
        }

        $days = floor($hours / 24);
        $hours = $hours % 24;

        if ($hours >= 12) {
            $days++;
        }

        return $days . " Days";
    }


    /**
     * Take an integer and a word and output it with it's appropriate plural suffix, if required
     *
     * @param int $int The number of the $word
     * @param string $word The word
     * @param string $plural The text to append to $word if there are multiple (or zero)
     *
     * @return string
     */
    public static function plural($int, $word, $plural = "s")
    {
        $return = $int . " " . $word;

        if ($int != 1) {
            $return .= $plural;
        }

        return $return;
    }


    /**
     * Send a location header.
     * Then kill the script, incase some output has already been output, we don't want the page to carry on outputing for security reasons.
     *
     * @param string $url The url to redirect to
     *
     * @return void
     */
    public static function redirect($url)
    {
        header("location: " . $url);
        die();
    }
}
