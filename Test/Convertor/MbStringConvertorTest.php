<?php
namespace Tcc\Test\Convertor;

use Tcc\Convertor\MbStringConvertor;
use Tcc\Test\Convertor\Mock\MockConvertToStrategy;
use Tcc\Test\Convertor\Mock\MockConvertFile;
use SplFileObject;
use SplTempFileObject;
use PHPUnit_Framework_TestCase;

class MbStringConvertorTest extends PHPUnit_Framework_TestCase
{
    protected $convertor;

    public function setUp()
    {
        $this->convertor = new MbStringConvertor;
    }

    public function testGetName()
    {
        $this->assertEquals('mbstring', $this->convertor->getName());
    }

    public function testDoConvert()
    {
        $convertor         = $this->convertor;
        $convertToStrategy = new MockConvertToStrategy;
        $convertor->setConvertToStrategy($convertToStrategy);

        $convertFile = new MockConvertFile();

        $convertFile->setIterator(new SplFileObject('./_files/foo.txt'));
        $convertFile->setInputCharset('GBK');
        $convertFile->setOutputCharset('UTF-8');

        $convertor->convert($convertFile);

        $this->assertSame('ANSI编码', $convertToStrategy->getConverted());
    }

    public function testDoConvertErrorCanRaiseException()
    {
        $this->setExpectedException('Exception');

        $convertor         = $this->convertor;
        $convertToStrategy = new MockConvertToStrategy;
        $convertor->setConvertToStrategy($convertToStrategy);

        $convertFile = new MockConvertFile();

        $convertFile->setIterator(new SplTempFileObject);
        $convertFile->setInputCharset('not-exists');
        $convertFile->setOutputCharset('UTF-8');

        $convertor->convert($convertFile);
    }
}
