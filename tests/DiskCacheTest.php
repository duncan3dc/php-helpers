<?php

namespace duncan3dc\Helpers;

class DiskCacheTest extends \PHPUnit_Framework_TestCase
{

    public function testCheck1()
    {
        $this->assertNull(DiskCache::check("example_key1"));
    }

    public function testCheck2()
    {
        $this->assertNull(DiskCache::get("example_key1"));
    }

    public function testCheck3()
    {
        $check = time();
        DiskCache::set("example_key2", "saved_data2");
        $this->assertSame($check, DiskCache::check("example_key2"));
    }

    public function testGet1()
    {
        DiskCache::set("example_key3", "saved_data2");
        $this->assertSame("saved_data2", DiskCache::get("example_key3"));
    }

    public function testGet2()
    {
        DiskCache::set("example_key4", false);
        $this->assertSame(false, DiskCache::get("example_key4"));
    }

    public function testGet3()
    {
        DiskCache::set("example_key5", []);
        $this->assertSame([], DiskCache::get("example_key5"));
    }

    public function testClear1()
    {
        DiskCache::set("example_key6", 0);
        DiskCache::clear("example_key6");
        $this->assertNull(DiskCache::get("example_key6"));
    }

    public function testClear2()
    {
        DiskCache::set("example_key7", 0);
        DiskCache::set("example_key8", "0");
        DiskCache::clear("example_key7");
        $this->assertSame("0", DiskCache::get("example_key8"));
    }

    public function testCall1()
    {
        DiskCache::call("test", function() {
            return "ok";
        });

        $result = DiskCache::call("test", function() {
            throw new \Exception("I SHOULD NEVER RUN!");
        });

        $this->assertSame("ok", $result);
    }

    public function testCall2()
    {
        DiskCache::call("call_key1", function() {
            return "data1";
        });

        DiskCache::call("call_key2", function() {
            return "data2";
        });

        $data1 = DiskCache::call("call_key1", function() {
            throw new \Exception("I SHOULD NEVER RUN!");
        });

        $data2 = DiskCache::call("call_key2", function() {
            throw new \Exception("I SHOULD NEVER RUN!");
        });

        $this->assertSame("data1", $data1);
        $this->assertSame("data2", $data2);
    }
}
