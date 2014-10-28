<?php

namespace duncan3dc\Helpers;

class Dict
{

    /**
     * Get an element from an array, or the default if it is not set or it's value is falsy.
     *
     * @param array $data The array to get the element from
     * @param mixed $key The key within the array to get
     * @param mixed $default The default value to return if no element exists or it's value is falsy
     *
     * @return mixed
     */
    public static function value(array $data, $key, $default = null)
    {
        $value = static::valueIfSet($data, $key, $default);

        if ($value) {
            return $value;
        } else {
            return $default;
        }
    }


    /**
     * Get an element from an array, or the default if it is not set.
     *
     * @param array $data The array to get the element from
     * @param mixed $key The key within the array to get
     * @param mixed $default The default value to return if no element exists in the array or it's value is null
     *
     * @return mixed
     */
    public static function valueIfSet(array $data, $key, $default = null)
    {
        if (isset($data[$key])) {
            return $data[$key];
        } else {
            return $default;
        }
    }


    /**
     * Convenience method to retrieve an element from $_GET superglobal.
     *
     * @param mixed $key The key from $_GET to get
     * @param mixed $default The default value to return if no element exists or it's value is falsy
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return static::value($_GET, $key, $default);
    }


    /**
     * Convenience method to retrieve an element from $_GET superglobal.
     *
     * @param mixed $key The key from $_GET to get
     * @param mixed $default The default value to return if no element exists or it's value is null
     *
     * @return mixed
     */
    public static function getIfSet($key, $default = null)
    {
        return static::valueIfSet($_GET, $key, $default);
    }


    /**
     * Convenience method to retrieve an element from $_POST superglobal.
     *
     * @param mixed $key The key from $_POST to get
     * @param mixed $default The default value to return if no element exists or it's value is falsy
     *
     * @return mixed
     */
    public static function post($key, $default = null)
    {
        return static::value($_POST, $key, $default);
    }


    /**
     * Convenience method to retrieve an element from $_POST superglobal.
     *
     * @param mixed $key The key from $_POST to get
     * @param mixed $default The default value to return if no element exists or it's value is null
     *
     * @return mixed
     */
    public static function postIfSet($key, $default = null)
    {
        return static::valueIfSet($_POST, $key, $default);
    }
}
