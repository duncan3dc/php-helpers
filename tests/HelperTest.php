<?php

namespace duncan3dc\Helpers;

use duncan3dc\Serial\ArrayObject as SerialObject;

class HelperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown parameter (test)
     */
    public function testGetOptions1()
    {
        Helper::getOptions([
            "test"  =>  "",
        ], []);
    }


    public function testGetOptions2()
    {
        $check = Helper::getOptions([
            "test"  =>  "override",
        ], [
            "test"  =>  "default",
        ]);
        $this->assertSame("override", $check["test"]);
    }


    public function testGetOptions3()
    {
        $check = Helper::getOptions([], [
            "test"  =>  "default",
        ]);
        $this->assertSame("default", $check["test"]);
    }


    public function testGetAnyOptions1()
    {
        $check = Helper::getAnyOptions([
            "test"  =>  "override",
        ], [
            "test"  =>  "default",
        ]);
        $this->assertSame("override", $check["test"]);
    }


    public function testGetAnyOptions2()
    {
        $check = Helper::getAnyOptions([], [
            "test"  =>  "default",
        ]);
        $this->assertSame("default", $check["test"]);
    }


    public function testGetAnyOptions3()
    {
        $check = Helper::getAnyOptions([
            "test"  =>  "ok",
        ], []);
        $this->assertSame("ok", $check["test"]);
    }


    public function testToArray1()
    {
        $check = Helper::toArray(["test" => "ok"]);
        $this->assertSame(["test" => "ok"], $check);
    }
    public function testToArray2()
    {
        $check = Helper::toArray("test");
        $this->assertSame(["test"], $check);
    }
    public function testToArray3()
    {
        $check = Helper::toArray(new \ArrayObject(["test" => "ok"]));
        $this->assertSame(["test" => "ok"], $check);
    }
    public function testToArray4()
    {
        if (!class_exists(SerialObject::class)) {
            $this->markTestSkipped("SerialObject does not exist");
        }
        $check = Helper::toArray(new SerialObject(["test" => "ok"]));
        $this->assertSame(["test" => "ok"], $check);
    }
}
