<?php
namespace Tcc\Test\Convertor;

use Tcc\ConvertFile\ConvertFile;
use Tcc\Test\Convertor\TestAssert\FooConvertor;
use Tcc\Test\Convertor\TestAssert\FooConvertToStrategy;
use PHPUnit_Framework_TestCase;

class AbstractConvertorTest extends PHPUnit_Framework_TestCase
{
    protected $convertor;

    public function setUp()
    {
        $this->convertor = new FooConvertor;
    }

    public function testSetTargetLocation()
    {
        $convertor = $this->convertor;
        $location  = './Convertor/_files/target-location';

        $return = $convertor->setTargetLocation($location);
        $this->assertSame($convertor, $return);
        $this->assertFileExists($location);
        if (!rmdir($location)) {
            $this->fail('Can not remove the created directory');
        }
    }

    public function testSetTargetLoationWillRaiseExceptionIfPassNonStringValue()
    {
        $this->setExpectedException('InvalidArgumentException',
            'Invalid location type: boolean');
        $convertor = $this->convertor;

        $convertor->setTargetLocation(false);
    }

    public function testGetTargetLocation()
    {
        $convertor = $this->convertor;

        $convertor->setTargetLocation(__DIR__);
        $this->assertEquals(str_replace('\\', '/', __DIR__),
            $convertor->getTargetLocation());
    }

    public function testGetTargetLocationWillRaiseExceptionIfNotSetBefore()
    {
        $this->setExpectedException('RuntimeException',
            'targetLocation has not been setted');
        $convertor = $this->convertor;

        $convertor->getTargetLocation();
    }

    public function testSetConvertToStrategy()
    {
        $convertor = $this->convertor;

        $return = $convertor->setConvertToStrategy(new FooConvertToStrategy);
        $this->assertSame($convertor, $return);
    }

    public function testGetConvertToStrategy()
    {
        $convertor         = $this->convertor;
        $convertToStrategy = new FooConvertToStrategy;

        $convertor->setConvertToStrategy($convertToStrategy);
        $this->assertSame($convertToStrategy, $convertor->getConvertToStrategy());
    }

    public function testGetConvertToStrategyCanReturnADefaultOneIfNotSet()
    {
        $convertor = $this->convertor;

        $convertToStrategy = $convertor->getConvertToStrategy();
        $this->assertInstanceOf(
            'Tcc\Convertor\ConvertToStrategy\LongNameConvertToStrategy',
            $convertToStrategy);
    }

    public function testConvert()
    {
        $convertor = $this->convertor;

        $convertToStrategy = $this->getMock(
            'Tcc\\Test\\Convertor\\TestAssert\\FooConvertToStrategy');
        $convertToStrategy->expects($this->once())
                          ->method('reset');

        $convertor->setConvertToStrategy($convertToStrategy);

        $convertFile = new ConvertFile('./Convertor/_files/foo.txt',
            'in-charset', 'out-charset');

        $convertor->convert($convertFile);
    }

    public function testConvertError()
    {
        $errMsg = 'Unable to convert file: foo.txt with input charset: in-charset'
                . ' and output charset: out-charset';
        $this->setExpectedException('RuntimeException', $errMsg);

        $convertor = $this->convertor;
        $convertor->setTriggerConvertErrorFlag(true);

        $convertToStrategy = $this->getMock(
            'Tcc\\Test\\Convertor\\TestAssert\\FooConvertToStrategy');
        $convertToStrategy->expects($this->once())
                          ->method('restoreConvert');

        $convertor->setConvertToStrategy($convertToStrategy);

        $convertFile = new ConvertFile('./Convertor/_files/foo.txt',
            'in-charset', 'out-charset');

        $convertor->convert($convertFile);
        $this->assertNull($convertor->getConvertFile());
    }
}
