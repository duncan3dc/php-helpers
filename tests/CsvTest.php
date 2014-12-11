<?php

namespace duncan3dc\Helpers;

class CsvTest extends \PHPUnit_Framework_TestCase
{
    private $path;
    private $file;
    private $data;

    public function setUp()
    {
        error_reporting(E_ALL);
        Csv::$lineEnding = "\n";

        if (!$this->path) {
            $this->path = __DIR__ . "/files/file1.csv";
            $this->file = new Csv($this->path);
            $this->data = [
                ["field1a", "field2a", "field3a"],
                ["field1b", "field2b", "field3b"],
            ];
        }
    }


    public function testPutGet()
    {
        $this->assertSame($this->data, $this->file->put($this->data)->get());
    }


    public function testAppend()
    {
        $data = ["field1c", "field2c", "field3c"];
        $check = $this->data;
        $check[] = $data;
        $this->assertSame($check, $this->file->append([$data])->get());
    }


    public function testStaticPut()
    {
        $path = tempnam("/tmp", "phpunit_csv_");

        Csv::putContents($path, [$this->data[0]]);

        $this->assertSame("field1a,field2a,field3a\n", file_get_contents($path));
        unlink($path);
    }


    public function testStaticGet()
    {
        $path = tempnam("/tmp", "phpunit_csv_");

        $file = fopen($path, "w");
        foreach ($this->data as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        $this->assertSame($this->data, Csv::getContents($path));
        unlink($path);
    }


    public function testArrayToString1()
    {
        $this->assertSame("\"ok ok\",ok\n", Csv::arrayToString([["ok ok", "ok"]]));
    }


    public function testArrayToString2()
    {
        $this->assertSame("\"ok \"\"ok\",ok\n", Csv::arrayToString([["ok \"ok", "ok"]]));
    }


    public function testArrayToString3()
    {
        $this->assertSame("\"secret,comma\",ok\n", Csv::arrayToString([["secret,comma", "ok"]]));
    }


    public function testLineEnding()
    {
        Csv::$lineEnding = "\r\n";
        $this->assertSame("test\r\n", Csv::arrayToString([["test"]]));
    }


    public function testDelimiter1()
    {
        Csv::$delimiter = ";";
        $this->assertSame("test1;\"other stuff\"\n", Csv::arrayToString([["test1", "other stuff"]]));
    }


    public function testDelimiter2()
    {
        Csv::$delimiter = ";";
        $this->assertSame("test1;other,stuff\n", Csv::arrayToString([["test1", "other,stuff"]]));
    }


    public function testDelimiter3()
    {
        Csv::$delimiter = ";";
        $this->assertSame("test1;\"other;stuff\"\n", Csv::arrayToString([["test1", "other;stuff"]]));
    }


    private function expectRuntimeException()
    {
        error_reporting(\E_ALL ^ \E_WARNING);
        $this->setExpectedException("RuntimeException");
    }


    public function testStaticPutFail()
    {
        $this->expectRuntimeException();
        Csv::putContents($this->path . "/does-not-exist", []);
    }


    public function testStaticGetFail()
    {
        $this->expectRuntimeException();
        Csv::getContents($this->path . "/does-not-exist", []);
    }


    public function testStaticAppendFail()
    {
        $this->expectRuntimeException();
        Csv::appendContents($this->path . "/does-not-exist", []);
    }
}
