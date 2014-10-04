<?php

namespace duncan3dc\Helpers;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    public function testCheck1()
    {
        $this->assertFalse(Cache::check("example_key1"));
    }

    public function testCheck2()
    {
        $this->assertNull(Cache::get("example_key1"));
    }

    public function testCheck3()
    {
        Cache::set("example_key2", "saved_data2");
        $this->assertTrue(Cache::check("example_key2"));
    }

    public function testGet1()
    {
        Cache::set("example_key3", "saved_data2");
        $this->assertSame("saved_data2", Cache::get("example_key3"));
    }

    public function testGet2()
    {
        Cache::set("example_key4", false);
        $this->assertSame(false, Cache::get("example_key4"));
    }

    public function testGet3()
    {
        Cache::set("example_key5", 0);
        $this->assertSame(0, Cache::get("example_key5"));
    }

    public function testClear1()
    {
        Cache::set("example_key6", 0);
        Cache::clear("example_key6");
        $this->assertNull(Cache::get("example_key6"));
    }

    public function testClear2()
    {
        Cache::set("example_key7", 0);
        Cache::set("example_key8", "0");
        Cache::clear("example_key7");
        $this->assertSame("0", Cache::get("example_key8"));
    }

    public function testClear3()
    {
        Cache::set("example_key9", "saved_data3");
        Cache::clear();
        $this->assertNull(Cache::get("example_key9"));
    }

    public function testCall1()
    {
        Cache::call("test", function() {
            return "ok";
        });

        $result = Cache::call("test", function() {
            throw new \Exception("I SHOULD NEVER RUN!");
        });

        $this->assertSame("ok", $result);
    }

    public function testCall2()
    {
        Cache::call("call_key1", function() {
            return "data1";
        });

        Cache::call("call_key2", function() {
            return "data2";
        });

        $data1 = Cache::call("call_key1", function() {
            throw new \Exception("I SHOULD NEVER RUN!");
        });

        $data2 = Cache::call("call_key2", function() {
            throw new \Exception("I SHOULD NEVER RUN!");
        });

        $this->assertSame("data1", $data1);
        $this->assertSame("data2", $data2);
    }
}
