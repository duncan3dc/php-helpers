<?php

namespace duncan3dc\Helpers;

class CsvTest extends \PHPUnit_Framework_TestCase
{
    protected $data;
    protected $csv;

    public function setUp()
    {
        error_reporting(E_ALL);

        $this->data = [
            ["field1a", "field2a", "field3a"],
            ["field1b", "field2b", "field3b"],
        ];

        $this->csv = new Csv;
        foreach ($this->data as $row) {
            $this->csv->addRow($row);
        }
    }


    protected function assertSameAsTestFile($filename, $string)
    {
        $this->assertSame(File::getContents(__DIR__ . "/files/" . $filename . ".csv"), $string);
    }


    protected function assertRowEqualsString($string, array $row)
    {
        $result = (new Csv)->addRow($row)->asString();
        $this->assertSame($string, $result);
    }


    public function testBasicAddRows()
    {
        $this->assertSameAsTestFile("basic", $this->csv->asString());
    }


    public function testCastAsString()
    {
        $this->assertSameAsTestFile("basic", (string) $this->csv);
    }


    public function testWrite()
    {
        $path = tempnam("/tmp", "phpunit_csv_");
        $this->csv->write($path);

        $this->assertSameAsTestFile("basic", File::getContents($path));
    }


    public function testClear()
    {
        $this->assertSame("", $this->csv->clear()->asString());
    }


    public function testSpacesInFields()
    {
        $this->assertRowEqualsString("\"ok ok\",ok\n", ["ok ok", "ok"]);
    }


    public function testQuotesInFields()
    {
        $this->assertRowEqualsString("\"ok \"\"ok\",ok\n", ["ok \"ok", "ok"]);
    }


    public function testCommaInFields()
    {
        $this->assertRowEqualsString("\"secret,comma\",ok\n", ["secret,comma", "ok"]);
    }


    public function testLineEnding()
    {
        $this->csv->setLineEnding("\r\n");
        $this->assertSameAsTestFile("crlf", $this->csv->asString());
    }


    public function testDelimiter()
    {
        $csv = new Csv;
        $csv->addRow(["test1", "other stuff"]);
        $csv->addRow(["test1", "other,stuff"]);
        $csv->addRow(["test1", "other;stuff"]);

        $csv->setDelimiter(";");

        $this->assertSameAsTestFile("delimiter", $csv->asString());
    }


    public function testDefinedFields()
    {
        $csv = new Csv;
        $csv->defineFields(["artist", "album", "year"]);
        $csv->addRow([
            "album"     =>  "duality",
            "artist"    =>  "set it off",
        ]);
        $this->assertSame("\"set it off\",duality,\n", $csv->asString());
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


    private function expectRuntimeException()
    {
        error_reporting(\E_ALL ^ \E_WARNING);
        $this->setExpectedException("RuntimeException");
    }


    public function testStaticPutFail()
    {
        $this->expectRuntimeException();
        Csv::putContents(__DIR__ . "/does-not-exist/file.csv", []);
    }


    public function testStaticGetFail()
    {
        $this->expectRuntimeException();
        Csv::getContents(__DIR__ . "/does-not-exist/file.csv", []);
    }
}
