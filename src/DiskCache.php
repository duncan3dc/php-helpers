<?php

namespace duncan3dc\Helpers;

class DiskCache
{
    public static $path = "/tmp/cache";


    protected static function path($filename)
    {
        if ($filename[0] != "/") {
            $filename = static::$path . "/" . $filename;
        }

        if (substr($filename, -5) != ".json") {
            $filename .= ".json";
        }

        $path = pathinfo($filename, PATHINFO_DIRNAME);

        # Ensure the cache directory exists
        if (!is_dir($path)) {
            if (!mkdir($path, 0775, true)) {
                return false;
            }
        }

        return $filename;
    }


    public static function check($filename)
    {
        if (!$filename = static::path($filename)) {
            return false;
        }

        if (!file_exists($filename)) {
            return false;
        }

        return filemtime($filename);
    }


    public static function get($filename, $mins = 0)
    {
        if (!$cache = static::check($filename)) {
            return false;
        }

        $limit = time() - ($mins * 60);

        if (!$mins || $cache > $limit) {
            $filename = static::path($filename);
            $return = Json::decodeFromFile($filename);
            return $return;
        }

        return false;
    }


    public static function set($filename, $data)
    {
        if (!$filename = static::path($filename)) {
            return false;
        }

        Json::encodeToFile($filename, $data);

        return true;
    }


    public static function clear($filename)
    {
        if (!$filename = static::path($filename)) {
            return true;
        }

        if (file_exists($filename)) {
            return unlink($filename);
        }

        return true;
    }


    public static function call($key, callable $func)
    {
        $trace = debug_backtrace();
        if ($function = $trace[1]["function"]) {
            $key = $function . "_" . $key;
            if ($class = $trace[1]["class"]) {
                $key = str_replace("\\", "_", $class) . "_" . $key;
            }
        }

        if (static::check($key)) {
            return static::get($key);
        }

        $return = $func();

        static::set($key, $return);

        return $return;
    }
}
