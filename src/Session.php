<?php

namespace duncan3dc\Helpers;

class Session
{
    protected static $active = false;
    protected static $name = "";
    protected static $data = [];


    public static function name($name)
    {
        static::$name = $name;
        static::$data = [];
    }


    public static function start($name = null)
    {
        if ($name) {
            static::name($name);
        }

        if (!static::$name) {
            throw new \Exception("Cannot start session, no name has been specified");
        }

        session_cache_limiter(false);

        session_name(static::$name);

        session_start();

        # Grab the sessions data to respond to get()
        static::$data = $_SESSION;

        # Remove the lock from the session file
        session_write_close();

        static::$active = true;
    }


    /**
     * Whenever a key is read from session data, just get it from cache
     */
    public static function get($key)
    {
        if (!static::$active) {
            static::start();
        }

        if (!array_key_exists($key, static::$data)) {
            return false;
        }

        return static::$data[$key];
    }


    /**
     * Whenever a key is set, we need to start the session up again to store it
     * When session_start is called it attempts to send the cookie to the browser with the session id in.
     * However if some output has already been sent then this will fail, this is why we suppress errors on the call here
     * This is safe because we sent the cookie when the start() method was called
     */
    public static function set($data, $value = null)
    {
        if (!static::$active) {
            static::start();
        }

        # Check that at least one value has been changed before starting up the sesson
        $changed = false;
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (static::get($key) !== $val) {
                    $changed = true;
                    break;
                }
            }
        } else {
            if (static::get($data) !== $value) {
                $changed = true;
            }
        }

        # If none of the values have changed then don't write to session data
        if (!$changed) {
            return false;
        }

        @session_start();

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $_SESSION[$key] = $val;
            }
        } else {
            $_SESSION[$data] = $value;
        }

        static::$data = $_SESSION;

        session_write_close();

        return true;
    }


    /**
     * This is a convenience method to prevent having to do several checks/set for all persistant variables
     */
    public static function getSet($key, $default = null)
    {
        if (!static::$active) {
            static::start();
        }

        # If this key was just submitted via post then store it in the session data
        if (isset($_POST[$key]) && $val = $_POST[$key]) {
            static::set($key, $val);
            return $val;
        }

        # If this key is part of the get data then store it in session data
        if (isset($_GET[$key]) && $val = $_GET[$key]) {
            static::set($key, $val);
            return $val;
        }

        # Get the current value for this key from session data
        $val = static::get($key);

        # If there is no current value for this key then set it to the supplied default
        if (!$val) {
            $val = $default;
            static::set($key, $val);
        }

        return $val;
    }


    /**
     * Destroy the session and all it's data
     */
    public static function destroy()
    {
        @session_start();

        unset($_SESSION);

        setcookie(static::$name, "", time() - 86400, "/");

        session_destroy();

        static::$active = false;
        static::$data = [];
    }
}
