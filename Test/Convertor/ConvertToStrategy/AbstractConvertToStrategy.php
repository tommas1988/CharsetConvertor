<?php
namespace Tcc\Test\Convertor\ConvertToStrategy;

use Tcc\Test\Convertor\ConvertToStrategy\TestAssert\FooConvertToStrategy;
use Tcc\Test\Convertor\ConvertToStrategy\Mock\MockConvertor;
use SplFileObject;
use SplTempFileObject;
use PHPUnit_Framework_TestCase;

class AbstractConvertToStrategy extends PHPUnit_Framework_TestCase
{
    protected $strategy;
    protected $strategyClass = 'Tcc\Test\Convertor\ConvertToStrategy\TestAssert\FooConvertToStrategy';

    public function setUp()
    {
        $strategy = new FooConvertToStrategy();
        $strategy->setConvertor(new MockConvertor);

        $this->strategy = $strategy;
    }

    public function testConvertTo()
    {
        $contents = 'test contents';
        $tempFile = new SplTempFileObject();

        $strategy = $this->getMock($this->strategyClass);

        $strategy->expects($this->once())
                 ->method('getTargetFile')
                 ->will($this->returnValue($tempFile));

        $strategy->convertTo($contents);

        $this->assertSame($contents, $tempFile->fgets());
    }

    public function testConvertToCanRaiseExceptionWhenTargetFileIsInvalid()
    {
        $this->setExpectedException('RuntimeException');

        $strategy = $this->getMock($this->strategyClass);

        $strategy->expects($this->once())
                 ->method('getTargetFile')
                 ->will($this->returnValue('invalid'));

        $strategy->convertTo('something');
    }

    public function testConvertToCanRaiseExceptionWhenCanNotWriteToTargetFile()
    {
        $this->setExpectedException('Exception');

        $strategy = $this->getMock($this->strategyClass);

        $strategy->expects($this->once())
                 ->method('getTargetFile')
                 ->will($this->returnValue(new SplFileObject(__FILE__)));

        $strategy->convertTo('something');
    }

    public function testGetTargetFile()
    {
        $strategy = $this->strategy;

        $targetFile = $strategy->getTargetFile();
        $this->assertInstanceOf('SplFileObject', $targetFile);
    }

    public function testGetTargetFileCanReturnOneIfATargetFileAlreadyExists()
    {
        $strategy = $this->getMock($this->strategyClass);

        $strategy->expects($this->never())
                 ->method('generateTargetFileName');

        $targetFile = new SplTempFileObject;
        $strategy->setTargetFile($targetFile);

        $this->assertSame($targetFile, $strategy->getTargetFile());
    }

    public function testRestoreConvert()
    {
        $strategy = $this->strategy;
        $filename = './files/temp';

        $targetFile = new SplFileObject($filename, 'w');

        if (!file_exists($filename)) {
            $this->fail('Can not create temp file');
        }

        $strategy->setTargetFile($targetFile);
        $stargegy->restoreConvert();

        $this->assertFalse(file_exists($filename));
    }
}
