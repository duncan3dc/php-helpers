<?php

namespace duncan3dc\Helpers;

class Debug {

    protected static $on = false;
    protected static $time = 0;
    protected static $mode = "text";
    protected static $indent = 0;


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


    protected static function indent() {

        $char = (static::$mode == "html") ? "&nbsp;" : " ";

        for($i = 0; $i < static::$indent; $i++) {
            for($y = 0; $y < 4; $y++) {
                echo $char;
            }
        }

    }


    public static function text($message,$data=false) {

        if(!static::$on) {
            return;
        }

        echo "--------------------------------------------------------------------------------\n";
        static::indent();
        echo static::getTime() . " - " . $message . "\n";;
        if(is_array($data)) {
            print_r($data);
        } elseif($data) {
            static::indent();
            echo "    " . $data . "\n";
        }

    }


    public static function html($message,$data=false) {

        if(!static::$on) {
            return;
        }

        echo "<hr>";
        echo "<i>";
            static::indent();
            echo "<b>" . static::getTime() . " - " . $message . "</b>";
            if(is_array($data)) {
                Html::print_r($data);
            } elseif($data) {
                static::indent();
                echo "&nbsp;&nbsp;&nbsp;&nbsp;" . $data . "<br>";
            }
        echo "</i>";

    }


    public static function call($message,$function) {

        $time = microtime(true);

        static::output($message . " [START]");

        static::$indent++;

        $function();

        static::$indent--;

        static::output($message . " [END] (" . number_format(microtime(true) - $time,3) . ")");

    }



}
