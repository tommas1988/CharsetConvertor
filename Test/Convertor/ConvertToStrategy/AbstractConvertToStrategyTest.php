<?php
namespace Tcc\Test\Convertor\ConvertToStrategy;

use Tcc\Test\Convertor\ConvertToStrategy\TestAssert\FooConvertToStrategy;
use Tcc\Test\Convertor\ConvertToStrategy\TestAssert\FooConvertor;
use SplFileObject;
use SplTempFileObject;
use PHPUnit_Framework_TestCase;

class AbstractConvertToStrategyTest extends PHPUnit_Framework_TestCase
{
    protected $strategy;

    public function setUp()
    {
        $strategy = new FooConvertToStrategy();
        $strategy->setConvertor(new FooConvertor);

        $this->strategy = $strategy;
    }

    public function testSetConvertor()
    {
        $strategy = new FooConvertToStrategy;

        $return = $strategy->setConvertor(new FooConvertor);

        $this->assertSame($strategy, $return);
    }

    public function testConvertTo()
    {
        $strategy = $this->strategy;
        $contents = array('foo', 'bar');

        foreach ($contents as $content) {
            $strategy->convertTo($content);
        }

        $this->assertEquals(implode('', $contents),
            file_get_contents($strategy->generateTargetFileName()));
    }

    public function testConvertToWillRaiseExceptionIfCanNotWriteToTargetFile()
    {
        $this->setExpectedException('RuntimeException');

        $strategy = $this->getMock(get_class($this->strategy),
            array('getTargetFileObject'));

        $strategy->expects($this->once())
                 ->method('getTargetFileObject')
                 ->will($this->returnValue(new SplFileObject(__FILE__, 'r')));

        $strategy->convertTo('something');
    }

    public function testRestoreConvert()
    {
        $this->fail('There are some problem with convertFinish assertion and restoreConvert');
        
        $strategy = $this->strategy;
        $strategy->setTargetFilename(
            './Convertor/ConvertToStrategy/_files/temp.txt');
        
        $strategy->restoreConvert();

        $this->assertFalse(
            file_exists($strategy->generateTargetFileName()));
    }
}
