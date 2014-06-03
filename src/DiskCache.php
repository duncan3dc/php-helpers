<?php

namespace duncan3dc\Helpers;

class DiskCache {

    public static $path = "/tmp/cache";


    protected static function path($filename) {

        if($filename[0] != "/") {
            $filename = static::$path . "/" . $filename;
        }

        $path = pathinfo($filename,PATHINFO_DIRNAME);

        # Ensure the cache directory exists
        if(!is_dir($path)) {
            if(!mkdir($path,0775,true)) {
                return false;
            }
        }

        return $filename;

    }


    public static function check($filename) {

        if(!$filename = static::path($filename)) {
            return false;
        }

        if(!file_exists($filename)) {
            return false;
        }

        return filemtime($filename);

    }


    public static function get($filename,$mins=false) {

        if(!$cache = static::check($filename)) {
            return false;
        }

        $limit = time() - ($mins * 60);

        if(!$mins || $cache > $limit) {
            $filename = static::path($filename);
            $json = file_get_contents($filename);
            $return = json_decode($json,true);
            return $return;
        }

        return false;

    }


    public static function set($filename,$data) {

        if(!$filename = static::path($filename)) {
            return false;
        }

        $json = json_encode($data);

        file_put_contents($filename,$json);

        return true;

    }


    public static function clear($filename) {

        if(!$filename = static::path($filename)) {
            return true;
        }

        if(file_exists($filename)) {
            return unlink($filename);
        }

        return true;

    }


    public static function call($key,$func) {

        $trace = debug_backtrace();
        if($function = $trace[1]["function"]) {
            $key = $function . "::" . $key;
            if($class = $trace[1]["class"]) {
                $key = $class . "::" . $key;
            }
        }

        if(static::check($key)) {
            return static::get($key);
        }

        $return = $func();

        static::set($key,$return);

        return $return;

    }


}
