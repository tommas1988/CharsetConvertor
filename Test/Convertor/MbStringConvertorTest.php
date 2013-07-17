<?php
namespace Tcc\Test\Convertor;

use Tcc\Convertor\MbStringConvertor;
use Tcc\Test\Convertor\TestAssert\FooConvertToStrategy;
use Tcc\ConvertFile\ConvertFile;
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
        $convertToStrategy = new FooConvertToStrategy;
        $convertor->setConvertToStrategy($convertToStrategy);

        $convertFile = new ConvertFile('./Convertor/_files/foo.txt',
            'GBK', 'UTF-8');

        $convertor->convert($convertFile);

        $this->assertEquals('ANSI编码', $convertToStrategy->getConverted());
    }

    public function testDoConvertErrorWillRaiseException()
    {
        $errMsg = 'Unable to convert file: foo.txt with input charset: '
                . 'not-exists-charset and output charset: UTF-8';
        $this->setExpectedException('RuntimeException');

        $convertor         = $this->convertor;
        $convertToStrategy = new FooConvertToStrategy;
        $convertor->setConvertToStrategy($convertToStrategy);

        $convertFile = new ConvertFile('./Convertor/_files/foo.txt',
            'not-exists-charset', 'UTF-8');

        $convertor->convert($convertFile);
    }
}
