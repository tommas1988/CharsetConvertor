<?php
namespace Tcc\Test\ConvertFile;

use PHPUnit_Framework_TestCase;
use Tcc\ConvertFile\ConvertFile;
use SplFileInfo;

class ConvertFileTest extends PHPUnit_Framework_TestCase
{
    protected $convertFile;

    public function setUp()
    {
    	$this->convertFile = new ConvertFile(
            new SplFileInfo(__FILE__), 
            'icharset', 
            'ocharset');
    }

    public function testInitalWithNotFileRaiseException()
    {
        $this->setExpectedException('Exception');
        
        $convertFile = new ConvertFile(__DIR__, 'icharset', 'ocharset');
        $this->assertNull($convertFile);
    }

    public function testGetIterator()
    {
        $convertFile = $this->convertFile;

        $this->assertInstanceOf('SplFileObject', $convertFile->getIterator());
    }

    public function testGetInputCharset()
    {
        $convertFile = $this->convertFile;

        $this->assertEquals('icharset', $convertFile->getInputCharset());
    }

    public function testGetOutputCharset()
    {
        $convertFile = $this->convertFile;

        $this->assertEquals('ocharset', $convertFile->getOutputCharset());
    }

    public function testGetFilenameWithExtension()
    {
        $convertFile = $this->convertFile;

        $this->assertEquals('ConvertFileTest.php', $convertFile->getFilename());
    }

    public function testGetFilenameWithoutExtension()
    {
        $convertFile = $this->convertFile;

        $this->assertEquals('ConvertFileTest', $convertFile->getFilename(true));
    }

    public function testGetPath()
    {
        $convertFile = $this->convertFile;

        $path = trim(str_replace('\\', '/', __DIR__));
        $this->assertEquals($path, $convertFile->getPath());
    }

    public function testGetPathname()
    {
        $convertFile = $this->convertFile;

        $pathname = trim(str_replace('\\', '/', __FILE__));
        $this->assertEquals($pathname, $convertFile->getPathname());
    }

    public function testGetExtension()
    {
        $convertFile = $this->convertFile;

        $this->assertEquals('php', $convertFile->getExtension());
    }
}
