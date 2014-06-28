<?php

namespace duncan3dc\Helpers;

class Debug {

    protected static $on = false;
    protected static $time = 0;
    protected static $mode = "text";


    public static function on($mode="text") {
        if(static::$on) {
            return;
        }
        static::$on = true;
        static::$time = microtime(true);
        static::$mode = $mode;
    }


    public static function off() {
        static::$on = false;
        static::$time = 0;
    }


    protected static function getTime() {
        return number_format(microtime(true) - static::$time,3);
    }


    public static function output($message,$data=false) {

        if(static::$mode == "html") {
            static::html($message,$data);
        } else {
            static::text($message,$data);
        }

    }


    public static function text($message,$data=false) {

        if(!static::$on) {
            return;
        }

        echo "--------------------------------------------------------------------------------\n";
        echo static::getTime() . " - " . $message . "\n";;
        if(is_array($data)) {
            print_r($data);
        } elseif($data) {
            echo "\t" . $data . "\n";
        }

    }


    public static function html($message,$data=false) {

        if(!static::$on) {
            return;
        }

        echo "<hr>";
        echo "<i>";
            echo "<b>" . static::getTime() . " - " . $message . "</b>";
            if(is_array($data)) {
                Html::print_r($data);
            } elseif($data) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;" . $data . "<br>";
            }
        echo "</i>";

    }


    public static function call($message,$function) {

        $time = microtime(true);

        static::output($message . " [START]");

        $function();

        static::output($message . " [END] (" . number_format(microtime(true) - $time,3) . ")");

    }



}
