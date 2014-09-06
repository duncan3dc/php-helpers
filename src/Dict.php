<?php

namespace duncan3dc\Helpers;

class Dict
{

    public static function value(array $data, $key, $default = null)
    {
        $value = static::valueIfSet($data, $key, $default);

        if ($value) {
            return $value;
        } else {
            return $default;
        }
    }


    public static function valueIfSet(array $data, $key, $default = null)
    {
        if (isset($data[$key])) {
            return $data[$key];
        } else {
            return $default;
        }
    }


    public static function get($key, $default = null)
    {
        return static::value($_GET, $key, $default);
    }


    public static function getIfSet($key, $default = null)
    {
        return static::valueIfSet($_GET, $key, $default);
    }


    public static function post($key, $default = null)
    {
        return static::value($_POST, $key, $default);
    }


    public static function postIfSet($key, $default = null)
    {
        return static::valueIfSet($_POST, $key, $default);
    }
}
