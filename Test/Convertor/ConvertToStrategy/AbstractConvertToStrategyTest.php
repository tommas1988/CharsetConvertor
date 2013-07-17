<?php
namespace Tcc\Test\Convertor\ConvertToStrategy;

use Tcc\Test\Convertor\ConvertToStrategy\TestAssert\FooConvertToStrategy;
use Tcc\Test\Convertor\ConvertToStrategy\TestAssert\FooConvertor;
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
            file_get_contents($strategy->getTargetFileName()));
    }

    public function testReset()
    {
        $strategy = $this->strategy;

        $strategy->convertTo('something');
        $this->assertInstanceOf('SplFileObject', $strategy->getTargetFile());

        $strategy->reset();
        $this->assertNull($strategy->getTargetFile());
    }

    public function testRestoreConvert()
    {   
        $strategy = $this->strategy;
        
        $strategy->convertTo('something');
        $this->assertFileExists($strategy->getTargetFileName());

        $strategy->restoreConvert();
        $this->assertNull($strategy->getTargetFile());
        $this->assertFalse(
            file_exists($strategy->getTargetFileName()));
    }
}
