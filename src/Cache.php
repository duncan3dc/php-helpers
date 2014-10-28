<?php

namespace duncan3dc\Helpers;

class Cache
{
    /**
     * @var CacheInstance[] $instances Internal management of the instantiated cache instances
     */
    private static $instances = [];

    /**
     * @var array $data Internal storage of cached data
     */
    private static $data = [];


    /**
     * Get a named instance of CacheInstance for segregating cache data.
     *
     * @param string $name The name of the instance to get
     *
     * @return CacheInstance
     */
    public static function getInstance($name)
    {
        if (!isset(static::$instances[$name])) {
            static::$instances[$name] = new CacheInstance();
        }
        return static::$instances[$name];
    }


    /**
     * Check if the specified key has already been cached.
     *
     * @param string $key The key of the cached data
     *
     * @return boolean
     */
    public static function check($key)
    {
        if (array_key_exists($key, static::$data)) {
            return true;
        }

        return false;
    }


    /**
     * Get the stored value of the specified key.
     *
     * @param string $key The key of the cached data
     *
     * @return mixed
     */
    public static function get($key)
    {
        if (!static::check($key)) {
            return null;
        }
        return static::$data[$key];
    }


    /**
     * Set the specified key to the specified value.
     *
     * @param string $key The key of the cached data
     * @param string $value The value to storage against the key
     *
     * @return void
     */
    public static function set($key, $value)
    {
        static::$data[$key] = $value;
    }


    /**
     * Clear a key within the cache data, or call without an argument to clear all the cached data.
     *
     * @param string $key The key of the cached data
     *
     * @return void
     */
    public static function clear($key = null)
    {
        if ($key) {
            if (isset(static::$data[$key])) {
                unset(static::$data[$key]);
            }
        } else {
            static::$data = [];
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
            $key = $function . "::" . $key;
            if ($class = $trace[1]["class"]) {
                $key = $class . "::" . $key;
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
