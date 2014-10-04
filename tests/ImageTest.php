<?php

namespace duncan3dc\Helpers;

class ImageTest extends \PHPUnit_Framework_TestCase
{

    public function testGetExtensionPng()
    {
        $this->assertSame("png", Image::getExtension(__DIR__ . "/images/image1.png"));
    }


    public function testGetExtensionJpg()
    {
        $this->assertSame("jpg", Image::getExtension(__DIR__ . "/images/image2.jpg"));
    }

    public function testGetExtensionGif()
    {
        $this->assertSame("gif", Image::getExtension(__DIR__ . "/images/image3.gif"));
    }


    public function testGetFormatExtensionPng()
    {
        $tests = ["image/png", IMAGETYPE_PNG];
        foreach ($tests as $test) {
            $this->assertSame("png", Image::getFormatExtension($test));
        }
    }

    public function testGetFormatExtensionJpg()
    {
        $tests = ["image/jpg", "image/jpeg", "image/pjpeg", IMAGETYPE_JPEG];
        foreach ($tests as $test) {
            $this->assertSame("jpg", Image::getFormatExtension($test));
        }
    }

    public function testGetFormatExtensionGif()
    {
        $tests = ["image/gif", IMAGETYPE_GIF];
        foreach ($tests as $test) {
            $this->assertSame("gif", Image::getFormatExtension($test));
        }
    }

    public function testGetFormatExtensionAcceptable1()
    {
        $this->assertSame(null, Image::getFormatExtension("image/png", "jpg"));
    }

    public function testGetFormatExtensionAcceptable2()
    {
        $this->assertSame("png", Image::getFormatExtension("image/png", "png"));
    }

    public function testGetFormatExtensionAcceptable3()
    {
        $this->assertSame("png", Image::getFormatExtension("image/png", ["jpg", "png"]));
    }

    public function testGetFormatExtensionAcceptable4()
    {
        $this->assertSame(null, Image::getFormatExtension("image/png", ["jpg", "gif"]));
    }


    public function testGetDate()
    {
        $this->assertSame(null, Image::getDate(__DIR__ . "/images/image2.jpg"));
    }
}
