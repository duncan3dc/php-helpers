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


}
