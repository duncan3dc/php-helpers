<?php

namespace duncan3dc\Helpers;

class DictTest extends \PHPUnit_Framework_TestCase
{
    private $data = [];

    public function __construct()
    {
        $this->data = [
            "zero"  =>  0,
            "one"   =>  1,
            "two"   =>  2,
            "four"  =>  4,
        ];

        $_POST["int_zero"] = 0;
        $_POST["string_emtpy"] = "";
        $_POST["string_ok"] = "ok";
        $_GET = $_POST;
    }


    public function testValue1()
    {
        $this->assertSame(1, Dict::value($this->data, "one"));
    }
    public function testValue2()
    {
        $this->assertSame("default", Dict::value($this->data, "zero", "default"));
    }


    public function testValueIfSet1()
    {
        $this->assertSame(0, Dict::valueIfSet($this->data, "zero", "default"));
    }
    public function testValueIfSet2()
    {
        $this->assertSame(3, Dict::valueIfSet($this->data, "three", 3));
    }


    public function testGet1()
    {
        $this->assertSame(null, Dict::get("int_zero"));
    }
    public function testGet2()
    {
        $this->assertSame("default", Dict::get("string_empty", "default"));
    }


    public function testGetIfSet1()
    {
        $this->assertSame(0, Dict::getIfSet("int_zero"));
    }
    public function testGetIfSet2()
    {
        $this->assertSame("ok", Dict::getIfSet("does_not_exist", "ok"));
    }


    public function testPost1()
    {
        $this->assertSame(null, Dict::post("int_zero"));
    }
    public function testPost2()
    {
        $this->assertSame("default", Dict::post("string_empty", "default"));
    }


    public function testPostIfSet1()
    {
        $this->assertSame(0, Dict::postIfSet("int_zero"));
    }
    public function testPotIfSet2()
    {
        $this->assertSame("ok", Dict::postIfSet("does_not_exist", "ok"));
    }
}
