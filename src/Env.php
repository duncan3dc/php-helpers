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


    public static function getHostname() {

        return Cache::call("hostname",function() {

            # If the hostname is in the server array (usually set by apache) then use that
            if($host = $_SERVER["HTTP_HOST"]) {
                return $host;
            }

            # Otherwise use the uname function to get the hostname of this machine
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


    public static function getVar($var) {

        $vars = Cache::call("envvars.json",function() {

            $path = static::path("data/env.json");

            if(!$json = file_get_contents($path)) {
                throw new \Exception("Failed to load the configuration file (" . $path . ")");
            }

            $vars = json_decode($json,true);

            return Helper::toArray($vars);

        });

        return $vars[$var];

    }


}
