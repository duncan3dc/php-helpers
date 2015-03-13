<?php

namespace duncan3dc\Helpers;

class Session
{
    /**
     * @var bool $init Whether the session has been started or not.
     */
    protected static $init = false;

    /**
     * @var string $name The name of the session.
     */
    protected static $name = "";

    /**
     * @var array $data The cache of the session data.
     */
    protected static $data = [];


    /**
     * Set the name of the session to use.
     *
     * @param string $name The name of the session
     *
     * @return void
     */
    public static function name($name)
    {
        static::$init = null;
        static::$name = $name;
        static::$data = [];
    }


    /**
     * Ensure the session data is loaded into cache.
     *
     * @return void
     */
    protected static function init()
    {
        if (static::$init) {
            return;
        }
        static::$init = true;

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
    }


    /**
     * Get a value from the session data cache.
     *
     * @param string $key The name of the name to retrieve
     *
     * @return mixed
     */
    public static function get($key)
    {
        static::init();

        if (!array_key_exists($key, static::$data)) {
            return;
        }

        return static::$data[$key];
    }


    /**
     * Set a value within session data.
     *
     * @param string|array $data Either the name of the session key to update, or an array of keys to update
     * @param mixed $value If $data is a string then store this value in the session data
     *
     * @return void
     */
    public static function set($data, $value = null)
    {
        static::init();

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
            return;
        }

        /**
         * Whenever a key is set, we need to start the session up again to store it
         * When session_start is called it attempts to send the cookie to the browser with the session id in.
         * However if some output has already been sent then this will fail, this is why we suppress errors on the call here
         */
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
    }


    /**
     * This is a convenience method to prevent having to do several checks/set for all persistant variables.
     * If the key name has been passed via POST then that value is stored in the session and returned.
     * If the key name has been passed via GET then that value is stored in the session and returned.
     * If there is already a value in the session data then that is returned.
     * If all else fails then the default value is returned.
     * All checks are truthy/falsy (so a POST value of "0" is ignored).
     *
     * @param string $key The name of the key to retrieve from session data
     * @param mixed $default The value to use if the current session value is falsy
     *
     * @return mixed
     */
    public static function getSet($key, $default = null)
    {
        static::init();

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
     * Tear down the session and wipe all it's data.
     *
     * @return void
     */
    public static function destroy()
    {
        @session_start();

        unset($_SESSION);

        setcookie(static::$name, "", time() - 86400, "/");
        unset($_COOKIE[static::$name]);

        session_destroy();

        # Reset the session data
        static::name(static::$name);
    }
}
