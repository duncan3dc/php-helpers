<?php

namespace duncan3dc\Helpers;

class CacheInstanceTest extends \PHPUnit_Framework_TestCase
{
    protected $cache;

    public function __construct()
    {
        $this->cache = new CacheInstance();
    }

    public function testCheck1()
    {
        $this->assertFalse($this->cache->check("example_key1"));
    }

    public function testCheck2()
    {
        $this->assertNull($this->cache->get("example_key1"));
    }

    public function testCheck3()
    {
        $this->cache->set("example_key2", "saved_data2");
        $this->assertTrue($this->cache->check("example_key2"));
    }

    public function testGet1()
    {
        $this->cache->set("example_key3", "saved_data2");
        $this->assertSame("saved_data2", $this->cache->get("example_key3"));
    }

    public function testGet2()
    {
        $this->cache->set("example_key4", false);
        $this->assertSame(false, $this->cache->get("example_key4"));
    }

    public function testGet3()
    {
        $this->cache->set("example_key5", 0);
        $this->assertSame(0, $this->cache->get("example_key5"));
    }

    public function testClear1()
    {
        $this->cache->set("example_key6", 0);
        $this->cache->clear("example_key6");
        $this->assertNull($this->cache->get("example_key6"));
    }

    public function testClear2()
    {
        $this->cache->set("example_key7", 0);
        $this->cache->set("example_key8", "0");
        $this->cache->clear("example_key7");
        $this->assertSame("0", $this->cache->get("example_key8"));
    }

    public function testClear3()
    {
        $this->cache->set("example_key9", "saved_data3");
        $this->cache->clear();
        $this->assertNull($this->cache->get("example_key9"));
    }

    public function testCall1()
    {
        $this->cache->call(function() {
            return "ok";
        });

        $result = $this->cache->call(function() {
            throw new \Exception("I SHOULD NEVER RUN!");
        });

        $this->assertSame("ok", $result);
    }

    public function testCall2()
    {
        $this->cache->call("call_key1", function() {
            return "data1";
        });

        $this->cache->call("call_key2", function() {
            return "data2";
        });

        $data1 = $this->cache->call("call_key1", function() {
            throw new \Exception("I SHOULD NEVER RUN!");
        });

        $data2 = $this->cache->call("call_key2", function() {
            throw new \Exception("I SHOULD NEVER RUN!");
        });

        $this->assertSame("data1", $data1);
        $this->assertSame("data2", $data2);
    }
}
