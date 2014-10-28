<?php

namespace duncan3dc\Helpers;

class CacheInstance
{
    /**
     * @var array $data Internal storage of cached data
     */
    private $data = [];


    /**
     * Check if the specified key has already been cached.
     *
     * @param string $key The key of the cached data
     *
     * @return boolean
     */
    public function check($key)
    {
        if (array_key_exists($key, $this->data)) {
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
    public function get($key)
    {
        if (!$this->check($key)) {
            return null;
        }
        return $this->data[$key];
    }


    /**
     * Set the specified key to the specified value.
     *
     * @param string $key The key of the cached data
     * @param mixed $value The value to storage against the key
     *
     * @return void
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }


    /**
     * Clear a key within the cache data, or call without an argument to clear all the cached data.
     *
     * @param string $key The key of the cached data
     *
     * @return void
     */
    public function clear($key = null)
    {
        if (isset($key)) {
            if (isset($this->data[$key])) {
                unset($this->data[$key]);
            }
        } else {
            $this->data = [];
        }
    }


    /**
     * Convience method to retrieve a value if it's cached, or run the callback and cache the data now if not.
     * The key parameter is optional, just a callback can be passed. If so the calling class/method will be used as the cache key.
     *
     * @param string $key The key of the cached data
     * @param callable $func A function to call that will return the value to cache
     *
     * @return mixed
     */
    public function call($key, callable $func = null)
    {
        if (!$func) {
            $func = $key;
            $key = null;

            $trace = debug_backtrace();
            if ($function = $trace[1]["function"]) {
                $key = $function . "::" . $key;
                if ($class = $trace[1]["class"]) {
                    $key = $class . "::" . $key;
                }
            }

            if (!$key) {
                throw new \Exception("No key provided for cache data");
            }
        }

        if ($this->check($key)) {
            return $this->get($key);
        }

        $return = $func();

        $this->set($key, $return);

        return $return;
    }
}
