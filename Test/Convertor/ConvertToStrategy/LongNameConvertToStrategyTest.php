<?php
namespace Tcc\Test\Convertor\ConvertToStrategy;

use Tcc\Convertor\ConvertToStrategy\LongNameConvertToStrategy;
use Tcc\Test\Convertor\ConvertToStrategy\TestAssert\FooConvertor;
use PHPUnit_Framework_TestCase;

class LongNameConvertToStrategyTest extends PHPUnit_Framework_TestCase
{
    protected $strategy;

    public function testGenerateTargetFileNameWithWinStyle()
    {
        $convertFile = $this->getMockBuilder('Tcc\ConvertFile\ConvertFile')
                            ->disableOriginalConstructor()
                            ->getMock();

        $convertFile->expects($this->once())
                    ->method('getPathname')
                    ->will($this->returnValue('C:/test/foo/bar/file'));

        $convertor = new FooConvertor;
        $convertor->setConvertFile($convertFile);
        $convertor->setTargetLocation('_files');

        $strategy = new LongNameConvertToStrategy;
        $strategy->setConvertor($convertor);

        $targetPathname = str_replace('\\', '/', getcwd())
                        . '/_files/test_foo_bar_file';
        $this->assertSame($targetPathname, $strategy->generateTargetFileName());
    }

    public function testGenerateTargetFileNameWithUnixStyle()
    {
        $convertFile = $this->getMockBuilder('Tcc\ConvertFile\ConvertFile')
                            ->disableOriginalConstructor()
                            ->getMock();

        $convertFile->expects($this->once())
                    ->method('getPathname')
                    ->will($this->returnValue('/test/foo/bar/file'));

        $convertor = new FooConvertor;
        $convertor->setConvertFile($convertFile);
        $convertor->setTargetLocation('_files');

        $strategy = new LongNameConvertToStrategy;
        $strategy->setConvertor($convertor);

        $targetPathname = str_replace('\\', '/', getcwd())
                        . '/_files/test_foo_bar_file';
        $this->assertSame($targetPathname, $strategy->generateTargetFileName());
    }
}
