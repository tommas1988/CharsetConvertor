<?php
namespace Tcc\Test\Convertor;

use Tcc\Test\Convertor\TestAssert\FooConvertor;
use Tcc\Test\Convertor\Mock\MockConvertToStrategy;
use Tcc\Test\ConvertFile\Mock\MockConvertFile;
use PHPUnit_Framework_TestCase;

class AbstractConvertorTest extends PHPUnit_Framework_TestCase
{
    protected $convertor;

    public function setUp()
    {
        $convertor = new FooConvertor;
    }

    public function testSetTargetLocation()
    {
        $convertor = $this->convertor;

        $return = $convertor->setTargetLocaion(__DIR__);
        $this->assertSame($convertor, $return);
    }

    public function testSetTargetLoationWithNonDirCanRaiseException()
    {
        $this->setExpectedException('Exception');
        $convertor = $this->convertor;

        $convertor->setTargetLocation('non-dir');
        $this->assertNull($convertor->getTargetLocation());
    }

    public function testGetTargetLocation()
    {
        $convertor = $this->convertor;

        $convertor->setTargetLocation(__DIR__);
        $this->assertEquals(str_replace('\\', '/', __DIR__), $convertor->getTargetLocation());
    }

    public function testGetTargetLocationThatDoseNotSetFirstCanRaiseException()
    {
        $this->setExpectedException('Exception');
        $convertor = $this->convertor;

        $convertor->getTargetLocation();
    }

    public function testSetConvertFile()
    {
        $convertor = $this->convertor;

        $return = $convertor->setConvertFile(new MockConvertFile);
        $this->assertSame($convertor, $return);
    }

    public function testGetConvertFile()
    {
        $convertor   = $this->convertor;
        $convertFile = new MockConvertFile;

        $convertor->setConvertFile($convertFile);
        $this->assertSame($convertFile, $convertor->getConvertingFile());
    }

    public function testSetConvertToStrategy()
    {
        $convertor = $this->convertor;

        $return = $convertor->setConvertToStrategy(new MockConvertToStrategy);
        $this->assertSame($convertor, $return);
    }

    public function testGetConvertToStrategy()
    {
        $convertor         = $this->convertor;
        $convertToStrategy = new MockConvertToStrategy;

        $convertor->setConvertToStrategy($convertToStrategy);
        $this->assertSame($convertToStrategy, $convertor->getConvertToStrategy());
    }

    public function testGetConvertToStrategyCanReturnADefaultOneIfNotSetFirst()
    {
        $convertor = $this->convertor;

        $convertToStrategy = $convertor->getConvertToStrategy();
        $this->assertInstanceOf('Tcc\Convertor\ConvertToStrategy\LongNameConvertToStrategy',
            $convertToStrategy);
    }

    public function testConvert()
    {
        $convertor   = $this->convertor;
        $convertFile = new MockConvertFile;

        $this->assertFalse($convertor->getConvertFinishFlag());
        $convertor->convert($convertFile);
        $this->assertTrue($convertor->getConverFinishFlag());
    }

    public function testConvertError()
    {
        $convertor   = $this->convertor;
        $convertFile = new MockConvertFile;
        $convertor->setTriggerConvertErrorFlag(true);

        $this->setExpectedException('Exception');
        $convertToStrategy = $this->getMockFromAbstract(
            'Tcc\\Convertor\\ConvertToStrategy\\AbstractConvertToStrategy');
        $convertToStrategy->expects($this->once())
                          ->method('restoreConvert');
                          
        $convertor->convert($convertFile);
        $this->assertFalse($convertor->convertFinish());
        $this->assertNull($convertor->getConvertFile());
    }

    public function testConvertFinish()
    {
        $convertor = $this->convertor;

        $convertor->setConvertFinishFlag(true);
        $this->assertTrue($convertor->convertFinish());
    }
}
