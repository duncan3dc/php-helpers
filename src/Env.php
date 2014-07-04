<?php

namespace duncan3dc\Helpers;

class Env {


    public static function getPath() {

        $path = Cache::call("path",function() {

            # If we have a document root (normally set via apache) then use that
            if($path = $_SERVER["DOCUMENT_ROOT"]) {
                return $path;
            }

            # Get the full path of the running script and use it's directory
            $path = realpath($_SERVER["PHP_SELF"]);
            return pathinfo($path,PATHINFO_DIRNAME);
        });

        if(!$path) {
            throw new \Exception("Failed to establish the current environment path");
        }

        return $path;
    }


    public static function path($append) {

        $path = static::getPath();

        if($append[0] != "/") {
            $path .= "/";
        }

        $path .= $append;

        return $path;
    }


    public static function getHostName() {

        return Cache::call("hostname",function() {

            # If the hostname is in the server array (usually set by apache) then use that
            if(!$uname && $host = $_SERVER["HTTP_HOST"]) {
                return $host;
            }

            # Otherwise use the get the hostname of this machine
            return static::getMachineName();
        });

    }


    public static function getMachineName() {

        return Cache::call("machine",function() {
            return php_uname("n");
        });

    }


    public static function getRevision($length=10) {

        $revision = Cache::call("revision",function() {

            $path = static::path(".git");
            if(!is_dir($path)) {
                return;
            }

            $head = $path . "/HEAD";
            if(!file_exists($head)) {
                return;
            }

            $data = file_get_contents($head);
            if(!preg_match("/ref: ([^\s]+)\s/",$data,$matches)) {
                return;
            }
            $ref = $path . "/" . $matches[1];
            if(!file_exists($ref)) {
                return;
            }

            return file_get_contents($ref);

        });

        if($length > 0) {
            return substr($revision,0,$length);
        } else {
            return $revision;
        }

    }


    public static function getVars() {

        return Cache::call("envvars.json",function() {

            $path = static::path("data/env.json");

            if(!file_exists($path)) {
                return [];
            }

            $json = file_get_contents($path);
            $vars = json_decode($json,true);

            return Helper::toArray($vars);

        });

    }


    public static function getVar($var) {

        return static::getVars()[$var];

    }


    public static function requireVar($var) {

        $vars = static::getVars();

        if(!array_key_exists($var,$vars)) {
            throw new \Exception("Failed to get the environment variable (" . $var . ")");
        }

        return $vars[$var];

    }


}
