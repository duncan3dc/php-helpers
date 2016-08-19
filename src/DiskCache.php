<?php

namespace duncan3dc\Helpers;

use duncan3dc\Serial\Json;

class DiskCache
{
    /**
     * @var string $path The base path to use for cache storage
     */
    public static $path = "/tmp/cache";


    /**
     * Generate the fullpath to the cache file based on the passed filename.
     *
     * @param string $filename The filename specified to store the cache in
     *
     * @return string
     */
    public static function path($filename)
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
            if (!mkdir($path, 0777, true)) {
                throw new \Exception("Unable to create cache directory (" . $path . ")");
            }
        }

        # Ensure directory is writable
        if (!is_writable($path)) {
            if (!chmod($path, 0777)) {
                throw new \Exception("Cache directory (" . $path . ") is not writable");
            }
        }

        return $filename;
    }


    /**
     * Check if the specified key has already been cached.
     * If it has been cached then the time the cached data was last modified is returned.
     *
     * @param string $filename The key of the cached data
     *
     * @return int|null
     */
    public static function check($filename)
    {
        if (!$filename = static::path($filename)) {
            return null;
        }

        if (!file_exists($filename)) {
            return null;
        }

        return filemtime($filename);
    }


    /**
     * Get the stored value of the specified key.
     *
     * @param string $filename The key of the cached data
     * @param int $mins The number of minutes to use the cache for, if the cache is present but older than this time then return null
     *
     * @return mixed
     */
    public static function get($filename, $mins = 0)
    {
        if (!$cache = static::check($filename)) {
            return null;
        }

        $limit = time() - ($mins * 60);

        if (!$mins || $cache > $limit) {
            $filename = static::path($filename);
            $return = Json::decodeFromFile($filename);

            if ($return instanceof \ArrayObject) {
                $return = $return->asArray();
            }

            if (array_key_exists("_disk_cache", $return)) {
                $return = $return["_disk_cache"];
            }

            return $return;
        }

        return null;
    }


    /**
     * Set the specified key to the specified value.
     *
     * @param string $filename The key of the cached data
     * @param string $data The value to storage against the key, this data must be JSON serializable
     *
     * @return void
     */
    public static function set($filename, $data)
    {
        $data = ["_disk_cache" => $data];
        $filename = static::path($filename);
        Json::encodeToFile($filename, $data);
    }


    /**
     * Clear a key within the cache data.
     *
     * @param string $filename The key of the cached data
     *
     * @return void
     */
    public static function clear($filename)
    {
        $filename = static::path($filename);

        if (!file_exists($filename)) {
            return;
        }

        if (!unlink($filename)) {
            throw new \Exception("Unable to delete file (" . $filename . ")");
        }
    }


    /**
     * Convience method to retrieve a value if it's cached, or run the callback and cache the data now if not.
     *
     * @param string $key The key of the cached data
     * @param callable $func A function to call that will return the value to cache
     *
     * @return mixed
     */
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
