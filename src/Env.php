<?php

namespace duncan3dc\Helpers;

class Env
{
    const PATH_DOCUMENT_ROOT    =   701;
    const PATH_PHP_SELF         =   702;
    const PATH_VENDOR_PARENT    =   703;
    protected static $path;
    protected static $vars;

    public static function usePath($path)
    {
        # Use the document root normally set via apache
        if ($path === self::PATH_DOCUMENT_ROOT) {
            if (!$path = realpath($_SERVER["DOCUMENT_ROOT"])) {
                throw new \Exception("DOCUMENT_ROOT not defined");
            }
            static::$path = $path;
            return;
        }

        # Get the full path of the running script and use it's directory
        if ($path === self::PATH_PHP_SELF) {
            if (!$path = realpath($_SERVER["PHP_SELF"])) {
                throw new \Exception("PHP_SELF not defined");
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
            throw new \Exception("Invalid path specified");
        }
    }


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


    public static function realpath($append)
    {
        $path = static::path($append);
        return realpath($path);
    }


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


    public static function getMachineName()
    {
        return Cache::call("machine", function() {
            return php_uname("n");
        });
    }


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

            $data = file_get_contents($head);
            if (!preg_match("/ref: ([^\s]+)\s/", $data, $matches)) {
                return;
            }
            $ref = $path . "/" . $matches[1];
            if (!file_exists($ref)) {
                return;
            }

            return file_get_contents($ref);
        });

        if ($length > 0) {
            return substr($revision, 0, $length);
        } else {
            return $revision;
        }
    }


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


    public static function getVar($var)
    {
        $vars = static::getVars();

        if (!array_key_exists($var, $vars)) {
            return null;
        }

        return $vars[$var];
    }


    public static function requireVar($var)
    {
        $vars = static::getVars();

        if (!array_key_exists($var, $vars)) {
            throw new \Exception("Failed to get the environment variable (" . $var . ")");
        }

        return $vars[$var];
    }


    public static function setVar($var, $value)
    {
        # Ensure the vars have been read from the disk
        static::getVars();

        static::$vars[$var] = $value;
    }


    public static function getUserAgent()
    {
        return $_SERVER["USER_AGENT"];
    }
}
