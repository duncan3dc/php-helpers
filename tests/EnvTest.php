<?php

namespace duncan3dc\Helpers;

use duncan3dc\Serial\Json;

class EnvTest extends \PHPUnit_Framework_TestCase
{
    protected $path;

    public function setUp()
    {
        Env::usePath(Env::PATH_PHP_SELF);
        $this->path = realpath(pathinfo($_SERVER["PHP_SELF"], PATHINFO_DIRNAME));

        Json::encodeToFile(Env::path("data/env.json"), [
            "test-string"   =>  "OK",
            "test-int"      =>  7,
            "test-boolean"  =>  true,
            "test-exists"   =>  false,
        ]);
    }

    public function testDocumentRoot1()
    {
        unset($_SERVER["DOCUMENT_ROOT"]);
        $this->setExpectedException("InvalidArgumentException");
        Env::usePath(Env::PATH_DOCUMENT_ROOT);
    }

    public function testDocumentRoot2()
    {
        $_SERVER["DOCUMENT_ROOT"] = "/tmp";
        Env::usePath(Env::PATH_DOCUMENT_ROOT);
        $this->assertSame($_SERVER["DOCUMENT_ROOT"], Env::getPath());
    }

    public function testGetPath()
    {
        $this->assertSame($this->path, Env::getPath());
    }

    public function testPath1()
    {
        $this->assertSame($this->path . "/", Env::path(""));
    }

    public function testPath2()
    {
        $this->assertSame($this->path . "/test", Env::path("test"));
    }

    public function testPath3()
    {
        $this->assertSame($this->path . "/test", Env::path("/test"));
    }

    public function testRealpath1()
    {
        $this->assertSame($this->path, Env::realpath(""));
    }

    public function testRealpath2()
    {
        $this->assertSame($this->path, Env::realpath("."));
    }

    public function testRealpath3()
    {
        $this->assertSame(realpath($this->path . "/.."), Env::realpath(".."));
    }

    public function testGetHostName()
    {
        $this->assertSame(php_uname("n"), Env::getHostName());
    }

    public function testGetMachineName()
    {
        $this->assertSame(php_uname("n"), Env::getMachineName());
    }

    public function testGetVar1()
    {
        $this->assertSame("OK", Env::getVar("test-string"));
    }

    public function testGetVar2()
    {
        $this->assertSame(7, Env::getVar("test-int"));
    }

    public function testGetVar3()
    {
        $this->assertSame(true, Env::getVar("test-boolean"));
    }

    public function testRequireVar1()
    {
        $this->assertSame(false, Env::requireVar("test-exists"));
    }

    public function testRequireVar2()
    {
        $exception = "";
        try {
            Env::requireVar("does-not-exist");
        } catch(\Exception $e) {
            $exception = $e->getMessage();
        }
        $this->assertSame("Failed to get the environment variable (does-not-exist)", $exception);
    }

    public function testSetVar1()
    {
        Env::setVar("test-new-var", "ok");
        $this->assertSame("ok", Env::getVar("test-new-var"));
    }
}
