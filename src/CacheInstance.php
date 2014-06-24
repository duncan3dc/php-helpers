<?php

namespace duncan3dc\Helpers;

class CacheInstance {

    private $data = [];


    public function check($key) {

        if(array_key_exists($key,$this->data)) {
            return true;
        }

        return false;

    }


    public function get($key) {

        return $this->data[$key];

    }


    public function set($key,$val) {

        $this->data[$key] = $val;

        return true;

    }


    public function clear($key=false) {

        if($key) {
            unset($this->data[$key]);

        } else {
            $this->data = [];

        }

        return true;

    }


    public function call($key,$func=false) {

        if(!$func) {
            $func = $key;
            $key = false;

            $trace = debug_backtrace();
            if($function = $trace[1]["function"]) {
                $key = $function . "::" . $key;
                if($class = $trace[1]["class"]) {
                    $key = $class . "::" . $key;
                }
            }

            if(!$key) {
                throw new \Exception("No key provided for cache data");
            }
        }

        if($this->check($key)) {
            return $this->get($key);
        }

        $return = $func();

        $this->set($key,$return);

        return $return;

    }


}
