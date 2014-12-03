<?php

namespace duncan3dc\Helpers;

class EnvTest extends \PHPUnit_Framework_TestCase
{
    private $path;
    private $file;
    private $data;

    public function setUp()
    {
        error_reporting(E_ALL);
        if (!$this->path) {
            $this->path = __DIR__ . "/files/file1.txt";
            $this->file = new File($this->path);
            $this->data = "ok1\nok2\n";
        }
    }


    public function testPutGet()
    {
        $this->assertSame($this->data, $this->file->put($this->data)->get());
    }


    public function testAppend()
    {
        $data = "ok3";
        $this->assertSame($this->data . $data, $this->file->append($data)->get());
    }


    public function testStaticPut()
    {
        unlink($this->path);
        File::putContents($this->path, $this->data);
        $this->assertSame($this->data, file_get_contents($this->path));
    }


    public function testStaticGet()
    {
        file_put_contents($this->path, $this->data);
        $this->assertSame($this->data, File::getContents($this->path));
    }


    private function expectRuntimeException()
    {
        error_reporting(\E_ALL ^ \E_WARNING);
        $this->setExpectedException("RuntimeException");
    }


    public function testStaticPutFail()
    {
        $this->expectRuntimeException();
        File::putContents($this->path . "/does-not-exist", $this->data);
    }


    public function testStaticGetFail()
    {
        $this->expectRuntimeException();
        File::getContents($this->path . "/does-not-exist", $this->data);
    }


    public function testStaticAppendFail()
    {
        $this->expectRuntimeException();
        File::appendContents($this->path . "/does-not-exist", $this->data);
    }
}
