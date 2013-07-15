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
            'in-charset', 
            'out-charset');
    }

    public function testConstructorWillRaiseExceptionIfConvertFileIsNotStringOrSplFileInfo()
    {
        $this->setExpectedException('InvalidArgumentException',
            'Invalid convert file');

        $convertFile = new ConvertFile(false, 'in-charset', 'out-charset');
        $this->assertNull($convertFile);
    }

    public function testConstructorWillRaiseExceptionIfConvertFileIsNotFileOrReadable()
    {
        $this->setExpectedException('InvalidArgumentException',
            'Convert file is not file or readable');
        
        $convertFile = new ConvertFile(__DIR__, 'in-charset', 'out-charset');
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

        $this->assertEquals('in-charset', $convertFile->getInputCharset());
    }

    public function testGetOutputCharset()
    {
        $convertFile = $this->convertFile;

        $this->assertEquals('out-charset', $convertFile->getOutputCharset());
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
