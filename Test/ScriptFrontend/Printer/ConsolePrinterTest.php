<?php
namespace Tcc\Test\ScriptFrontend\Printer;

use Tcc\ScriptFrontend\Runner;
use Tcc\ScriptFrontend\Printer\ConsolePrinter;
use Tcc\Test\ScriptFrontend\Printer\TestAssert\FooConvertFile;
use PHPUnit_Framework_TestCase;

class ConsolePrinterTest extends PHPUnit_Framework_TestCase
{
    protected $printer;

    public function setUp()
    {
    	$this->printer = new ConsolePrinter;
    }

    public function testSetAppRunner()
    {
    	$printer = $this->printer;
    	$result  = $printer->setAppRunner(new Runner);

    	$this->assertSame($printer, $result);
    }

    public function testGetAppRunner()
    {
    	$printer = $this->printer;

    	$runner = new Runner;
    	$printer->setAppRunner($runner);

    	$this->assertSame($runner, $printer->getAppRunner());
    }

    public function testGetAppRunnerWillRaiseExceptionIfNoRunnerSetBefore()
    {
        $this->setExpectedException('RuntimeException');

        $this->printer->getAppRunner();
    }

    public function testUpdateWithPreConvertState()
    {
    	$printer = $this->printer;

        ob_start();
        $printer->update(Runner::PRE_CONVERT);
        $result = ob_get_clean();

        $this->assertEquals("CharsetConvertor by Tommas Yuan\n",
            $result);
    }

    public function testUpdateWithConvertingState()
    {
        $printer = $this->printer;

        $runner = $this->getMock('Tcc\\ScriptFrontend\\Runner',
            array('convertFileCount'));

        $runner->expects($this->any())
               ->method('convertFileCount')
               ->will($this->returnValue(3));

        $runner->setConvertResult(new FooConvertFile)
               ->setConvertResult(new FooConvertFile, 'error');

        $printer->setAppRunner($runner);

        ob_start();
        $printer->update(Runner::CONVERTING);
        $result = ob_get_clean();

        $this->assertEquals("\r[#- ] 2/3", $result);
    }

    public function testUpdateWithConvertPostState()
    {
        $expects = "\n\nTotal Files: 3, convert failure: 1\nDone!"
                 . "\n\nFile name: convert-file-one path: convert-file-one-path"
                 . " input charset: in-charset output charset: out-charset"
                 . "\nFile name: convert-file-two path: convert-file-two-path"
                 . " input charset: in-charset output charset: out-charset\nError: error"
                 . "\nFile name: convert-file-three path: convert-file-three-path"
                 . " input charset: in-charset output charset: out-charset";

        $map = array(
            array(Runner::COUNT_ALL, 3),
            array(Runner::COUNT_FAILURE, 1),
        );

        $printer = $this->printer;
        $runner  = $this->getMock('Tcc\\ScriptFrontend\\Runner',
            array('convertFileCount'));

        $runner->expects($this->any())
               ->method('convertFileCount')
               ->will($this->returnValueMap($map));

        $convertFileOne = new FooConvertFile('convert-file-one',
            'in-charset', 'out-charset');
        $convertFileOne->setPath('convert-file-one-path');

        $convertFileTwo = new FooConvertFile('convert-file-two',
            'in-charset', 'out-charset');
        $convertFileTwo->setPath('convert-file-two-path');

        $convertFileThree = new FooConvertFile('convert-file-three',
            'in-charset', 'out-charset');
        $convertFileThree->setPath('convert-file-three-path');

        $runner->setOption('verbose', true)
               ->setConvertResult($convertFileOne)
               ->setConvertResult($convertFileTwo, 'error')
               ->setConvertResult($convertFileThree);

        $printer->setAppRunner($runner);

        ob_start();
        $printer->update(Runner::CONVERT_POST);
        $result = ob_get_clean();

        $this->assertEquals($expects, $result);
    }

    public function testUpdateWithUnknownState()
    {
        $this->setExpectedException('InvalidArgumentException');

        $printer = $this->printer;
        $printer->update('unknown-state');
    }
}
