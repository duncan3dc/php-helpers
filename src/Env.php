<?php

namespace duncan3dc\Helpers;

use duncan3dc\Serial\Json;

class Env
{
    /**
     * For use with usePath() - Represents the apache document root
     */
    const PATH_DOCUMENT_ROOT = 701;

    /**
     * For use with usePath() - Represents the directory that the PHP_SELF filename is in
     */
    const PATH_PHP_SELF = 702;

    /**
     * For use with usePath() - Represents the parent of the vendor directory (commonly the project root)
     */
    const PATH_VENDOR_PARENT = 703;

    /**
     * @var string $path The root path to use
     */
    protected static $path;

    /**
     * @var array $vars Internal cache of environment variables
     */
    protected static $vars;


    /**
     * Set the root path to use in the path methods.
     *
     * @param int|string $path Either one of the PATH class constants or an actual path to a directory that exists, and is readable
     *
     * @return void
     */
    public static function usePath($path)
    {
        # Use the document root normally set via apache
        if ($path === self::PATH_DOCUMENT_ROOT) {
            if (empty($_SERVER["DOCUMENT_ROOT"]) || !is_dir($_SERVER["DOCUMENT_ROOT"])) {
                throw new \InvalidArgumentException("DOCUMENT_ROOT not defined");
            }
            static::$path = $_SERVER["DOCUMENT_ROOT"];
            return;
        }

        # Get the full path of the running script and use it's directory
        if ($path === self::PATH_PHP_SELF) {
            if (empty($_SERVER["PHP_SELF"]) || !$path = realpath($_SERVER["PHP_SELF"])) {
                throw new \InvalidArgumentException("PHP_SELF not defined");
            }
            static::$path = pathinfo($path, PATHINFO_DIRNAME);
            return;
        }

        # Calculate the parent of the vendor directory and use that
        if ($path === self::PATH_VENDOR_PARENT) {
            static::$path = realpath(__DIR__ . "/../../../..");
            return;
        }

        if (is_dir($path)) {
            static::$path = $path;
        } else {
            throw new \InvalidArgumentException("Invalid path specified");
        }
    }


    /**
     * Get the root path, by default this is the parent directory of the composer vender directory.
     *
     * @return string
     */
    public static function getPath()
    {
        if (!static::$path) {
            static::usePath(self::PATH_VENDOR_PARENT);
        }

        if (!static::$path) {
            throw new \Exception("Failed to establish the current environment path");
        }

        return static::$path;
    }


    /**
     * Get an absolute path for the specified relative path (relative to the currently used internal root path).
     *
     * @param string $apend The relative path to append to the root path
     * @param int|string $use Either one of the PATH class constants or an actual path to a directory that exists, and is readable
     *
     * @return string
     */
    public static function path($append, $use = null)
    {
        $path = static::getPath();

        # If a different use has been requested then use it for this call only
        if ($use) {
            $previous = $path;
            static::usePath($use);
            $path = static::getPath();
            static::usePath($previous);
        }

        if (substr($append, 0, 1) != "/") {
            $path .= "/";
        }

        $path .= $append;

        return $path;
    }


    /**
     * Get an absolute path for the specified relative path, convert symlinks to a canonical path, and check the path exists.
     * This method is very similar to path() except the result is then run through php's standard realpath() function.
     *
     * @param string $append The relative path to append to the root path
     *
     * @return string
     */
    public static function realpath($append)
    {
        $path = static::path($append);
        return realpath($path);
    }


    /**
     * Get the current hostname from apache if this is mod_php otherwise the server's hostname.
     *
     * @return string
     */
    public static function getHostName()
    {
        return Cache::call("hostname", function() {

            # If the hostname is in the server array (usually set by apache) then use that
            if (isset($_SERVER["HTTP_HOST"]) && $host = $_SERVER["HTTP_HOST"]) {
                return $host;
            }

            # Otherwise use the get the hostname of this machine
            return static::getMachineName();
        });
    }


    /**
     * Get the current hostname of the machine.
     *
     * @return string
     */
    public static function getMachineName()
    {
        return Cache::call("machine", function() {
            return php_uname("n");
        });
    }


    /**
     * Get the revision number from the local git clone data.
     *
     * @param int $length The length of the revision hash to return
     *
     * @return string|void
     */
    public static function getRevision($length = 10)
    {
        $revision = Cache::call("revision", function() {
            $path = static::path(".git");
            if (!is_dir($path)) {
                return;
            }

            $head = $path . "/HEAD";
            if (!file_exists($head)) {
                return;
            }

            $data = File::getContents($head);
            if (!preg_match("/ref: ([^\s]+)\s/", $data, $matches)) {
                return;
            }
            $ref = $path . "/" . $matches[1];
            if (!file_exists($ref)) {
                return;
            }

            return File::getContents($ref);
        });

        if ($length > 0) {
            return substr($revision, 0, $length);
        } else {
            return $revision;
        }
    }


    /**
     * Get all defined environment variables.
     *
     * @return array
     */
    public static function getVars()
    {
        if (!is_array(static::$vars)) {
            $path = static::path("data/env.json");

            try {
                $vars = Json::decodeFromFile($path);
            } catch(\Exception $e) {
                $vars = [];
            }

            static::$vars = Helper::toArray($vars);
        }

        return static::$vars;
    }


    /**
     * Get a specific environment variable, or null if it doesn't exist.
     *
     * @param string $var The name of the variable to retrieve
     *
     * @return mixed
     */
    public static function getVar($var)
    {
        $vars = static::getVars();

        if (!array_key_exists($var, $vars)) {
            return null;
        }

        return $vars[$var];
    }


    /**
     * Get a specific environment variable, throw an exception if it doesn't exist.
     *
     * @param string $var The name of the variable to retrieve
     *
     * @return mixed
     */
    public static function requireVar($var)
    {
        $vars = static::getVars();

        if (!array_key_exists($var, $vars)) {
            throw new \Exception("Failed to get the environment variable (" . $var . ")");
        }

        return $vars[$var];
    }


    /**
     * Override an environment variable.
     *
     * @param string $var The name of the variable to set
     * @param string|int|boolean $value The value of the environment variable
     *
     * @return void
     */
    public static function setVar($var, $value)
    {
        # Ensure the vars have been read from the disk
        static::getVars();

        static::$vars[$var] = $value;
    }


    /**
     * Get the current useragent.
     *
     * @return string
     */
    public static function getUserAgent()
    {
        if (empty($_SERVER["USER_AGENT"])) {
            return "";
        }
        return $_SERVER["USER_AGENT"];
    }
}
