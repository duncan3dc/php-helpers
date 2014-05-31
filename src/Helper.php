<?php

namespace duncan3dc\Helpers;

class Helper {


    /**
     * Parse the options passed to cpdbase functions
     * Basically just merge the two arrays, giving user specified options the preference
     * Also ensures that each paramater in the user array is valid
     */
    public static function getOptions($userSpecified,$defaults) {

        $options = static::getAnyOptions($userSpecified,$defaults);

        foreach($options as $key => $null) {
            if(array_key_exists($key,$defaults)) {
                continue;
            }
            throw new \Exception("Unknown parameter (" . $key . ")");
        }

        return $options;

    }


    /**
     * This is a safe version of the getOptions() method
     * It allows any custom option key in the userSpecified array
     */
    public static function getAnyOptions($userSpecified,$defaults) {

        $options = $defaults;
        $userSpecified = static::toArray($userSpecified);

        foreach($userSpecified as $key => $val) {
            $options[$key] = $val;
        }

        return $options;

    }


    /**
     * Ensure that the passed parameter is a string, or an array of strings
     */
    public static function toString($data) {

        if(is_array($data)) {
            $newData = [];
            foreach($data as $key => $val) {
                $key = (string)$key;
                $newData[$key] = static::toString($val);
            }

        } else {
            $newData = (string)$data;

        }

        return $newData;

    }


    /**
     * Ensure that the passed parameter is an array
     */
    public static function toArray($value=false) {

        # If it's already an array then just pass it back
        if(is_array($value)) {
            return $value;
        }

        # If it's not an array then create a new array to be returned
        $array = [];

        # If a value was passed as a string/int then include it in the new array
        if($value) {
            $array[] = $value;
        }

        return $array;

    }


    public static function cleanupArray($array) {

        $newArray = array();

        $array = static::toArray($array);

        foreach($array as $key => $val) {

            if(is_array($val)) {
                $val = static::cleanupArray($val);

            } else {
                $val = trim($val);
                if(!$val) {
                    continue;
                }
            }

            $newArray[$key] = $val;

        }

        return $newArray;

    }


    public static function date($format,$date,$time=false) {

        if(!$date = trim($date)) {
            return 0;
        }

        if(preg_match("/[a-z]/i",$date)) {
            return 0;
        }

        # Define some time handling code to be used in several places below
        $timeFunc = function($time=false) {

            $return = [12,0,0];

            if(!$time) {
                return $return;
            }

            if(preg_match("/[a-z]/i",$date)) {
                return $return;
            }

            # Human readable format (h:i:s)
            if(strpos($time,":")) {
                return explode(":",$time);
            }

            # Sortable format (His)
            return array(
                floor($time / 10000),
                floor(($time / 100) % 100),
                $time % 100,
            );

        };

        # Sql date format (yyyy-mm-dd hh:ii:ss) (with optional milliseconds)
        if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})\s+([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]{6})?$/",$date,$matches)) {
            list($null,$y,$m,$d,$h,$i,$s) = $matches;
            $date = mktime($h,$i,$s,$m,$d,$y);

        # Human readable format (d/m/y or d-m-y)
        } elseif(strpos($date,"/") || strpos($date,"-")) {
            $char = (strpos($date,"/")) ? "/" : "-";
            if(!$time && strpos($date," ")) {
                list($date,$time) = explode(" ",$date);
            }
            list($d,$m,$y) = explode($char,$date);
            list($h,$i,$s) = $timeFunc($time);
            $date = mktime($h,$i,$s,$m,$d,$y);

        # Sortable format (YmdHi/YmdHis)
        } elseif($date > 200000000000) {
            $y = substr($date,0,4);
            $m = substr($date,4,2);
            $d = substr($date,6,2);
            $h = substr($date,8,2);
            $i = substr($date,10,2);
            $s = substr($date,12,2);
            $date = mktime($h,$i,$s,$m,$d,$y);

        # Sortable format (Date only - Ymd with optional separate time)
        } elseif($date < 99999999) {
            $y = substr($date,0,4);
            $m = substr($date,4,2);
            $d = substr($date,6,2);

            if(!$time && strpos($date," ")) {
                list($date,$time) = explode(" ",$date);
            }
            list($h,$i,$s) = $timeFunc($time);

            $date = mktime($h,$i,$s,$m,$d,$y);

        }

        $return = date($format,$date);

        # If the result looks like a number then return it as an int
        if(preg_match("/^[0-9]+$/",$return)) {
            # Don't attempt to cast a number out of the standard int range
            if($return < 2147483648) {
                $return = (int)$return;
            }
        }

        return $return;

    }


    public static function dateDiff($from,$to=false) {

        if(!$to) {
            $to = $from;
            $from = date("d/m/Y");
        }

        if(!$dateFrom = static::date("U",$from)) {
            return false;
        }

        if(!$dateTo = static::date("U",$to)) {
            return false;
        }

        $diff = $dateTo - $dateFrom;
        $days = round($diff / 86400);

        return $days;

    }


    public static function url($url,$params=false) {

        if(!is_array($params) || count($params) < 1) {
            return $url;
        }

        $pos = strpos($url,"?");

        # If there is no question mark in the url then set this as the first parameter
        if($pos === false) {
            $url .= "?";

        # If the question mark is the last character then no appending is required
        } elseif($pos != (strlen($url) - 1)) {

            # If the last character is not an ampersand then append one
            if(substr($url,-1) != "&") {
                $url .= "&";
            }

        }

        $url .= http_build_query($params);

        return $url;

    }


}
