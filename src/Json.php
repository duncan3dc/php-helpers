<?php

namespace duncan3dc\Helpers;

class Json {


    public static function convert($data,$options=false) {

        $options = Helper::getOptions($options,[
            "cleanup"   =>  false,
        ]);

        if(is_array($data)) {
            if($options["cleanup"]) {
                $data = Helper::cleanupArray($data);
            }
            return static::encode($data);

        } else {
            $array = static::decode($data);
            if($options["cleanup"]) {
                $array = Helper::cleanupArray($array);
            }
            return $array;

        }

    }


    public static function encode($data) {

        if(count($data) < 1) {
            return "";
        }

        $json = json_encode($data);

        static::checkLastError();

        return $json;

    }


    public static function decode($json) {

        if(!$json) {
            return [];
        }

        $data = json_decode($json,true);

        static::checkLastError();

        return $data;

    }


    public static function checkLastError() {

        $error = json_last_error();

        if($error == JSON_ERROR_NONE) {
            return;
        }

        $message = json_last_error_msg();

        throw new \Exception("JSON Error: " . $message,$error);

    }


}
