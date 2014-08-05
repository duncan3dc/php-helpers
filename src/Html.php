<?php

namespace duncan3dc\Helpers;

class Html
{

    #public static function print_r($data, $return = null)
    public static function printr($data, $return = null)
    {
        $output = "<pre>" . print_r($data, true) . "</pre>";

        if ($return) {
            return $output;
        }

        echo $output;
    }


    public static function price($val, $options = null)
    {
        $options = Helper::getOptions($options, [
            "class"     =>  "red",
            "currency"  =>  false,
            "symbol"    =>  "£",
            "symbolp"   =>  "",
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


    public static function getCurrencies($symbols = null)
    {
        $currencies = [
            "GBP"   =>  [
                "title"     =>  "Pound Sterling",
                "prefix"    =>  "£",
                "suffix"    =>  "",
            ],
            "EUR"   =>  [
                "title"     =>  "Euro",
                "prefix"    =>  "€",
                "suffix"    =>  "",
            ],
            "USD"   =>  [
                "title"     =>  "US Dollar",
                "prefix"    =>  "$",
                "suffix"    =>  "",
            ],
            "CZK"   =>  [
                "title"     =>  "Czech Koruna",
                "prefix"    =>  "",
                "suffix"    =>  "Kč",
            ],
            "PLN"   =>  [
                "title"     =>  "Polish Złoty",
                "prefix"    =>  "",
                "suffix"    =>  "zł",
            ],
            "SEK"   =>  [
                "title"     =>  "Swedish Krona",
                "prefix"    =>  "",
                "suffix"    =>  "kr",
            ],
            "RUB"   =>  [
                "title"     =>  "Russia Ruble",
                "prefix"    =>  "Р",
                "suffix"    =>  "",
            ],
            "ILS"   =>  [
                "title"     =>  "Israeli shekel",
                "prefix"    =>  "₪",
                "suffix"    =>  "",
            ],
        ];

        if ($symbols) {
            return $currencies;
        }

        $return = [];
        foreach ($currencies as $key => $val) {
            $return[$key] = $val["title"];
        }

        return $return;
    }


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
            while (strlen($string) > ($options["length"] + $options["extra"])) {
                $string = substr($string, 0, $options["length"]);
                $string = trim($string);

                $words = explode(" ", $string);
                array_pop($words);
                $string = implode(" ", $words);

                $string .= $options["suffix"];
            }
        } else {
            $length = $options["length"] + $options["extra"] + strlen($options["suffix"]);
            if (strlen($string) > $length) {
                $string = substr($string, 0, $length);
                $string .= $options["suffix"];
            }
        }

        return $string;
    }


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


    public static function entities($string)
    {
        return htmlentities($string, ENT_QUOTES, "UTF-8");
    }


    /**
     * Take a number of seconds and convert it to the relevant units (seconds, minutes, hours, etc)
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
     */
    public static function plural($int, $word, $plural = "s")
    {
        $return = $int . " " . $word;

        if ($int != 1) {
            $return .= $plural;
        }

        return $return;
    }


    public static function redirect($url)
    {
        header("location: " . $url);
        die();
    }
}
