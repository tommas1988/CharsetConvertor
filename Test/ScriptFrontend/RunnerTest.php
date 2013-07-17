<?php
namespace Tcc\Test\ScriptFrontend;

use Tcc\ScriptFrontend\Runner;
use Tcc\ScriptFrontend\Printer\ConsolePrinter;
use Tcc\Convertor\ConvertorFactory;
use Tcc\ConvertFile\ConvertFileAggregate;
use Tcc\Test\ScriptFrontend\TestAssert\FooConvertFile;
use Tcc\Test\ScriptFrontend\TestAssert\FooConvertor;
use Tcc\Test\ScriptFrontend\TestAssert\FooPrinter;
use PHPUnit_Framework_TestCase;

class RunnerTest extends PHPUnit_Framework_TestCase
{
    public function testParseCommandWillRaiseExceptionIfArgumentsIsEmpty()
    {
        $this->setExpectedException('RuntimeException');

        Runner::init(array());
    }

    public function testParseCommandWillStopAtHelpCommand()
    {
        $args = array('--help', 'undefined-command');

        ob_start();
        ConsolePrinter::printHelpInfo();
        $expects = ob_get_clean();

        ob_start();
        Runner::init($args)->run();
        $actual = ob_get_clean();

        $this->assertSame($expects, $actual);
    }

    public function testParseCommandWillStopAtVersionCommand()
    {
        $args = array('--version', 'undefined-command');

        ob_start();
        ConsolePrinter::printVersion();
        $expects = ob_get_clean();

        ob_start();
        Runner::init($args)->run();
        $actual = ob_get_clean();

        $this->assertSame($expects, $actual);
    }

    public function testParseCommandWillStopAtUndefinedCommand()
    {
        $args    = array('undefined-command', __FILE__);
        $expects = 'Unrecognize command: undefined-command';

        ob_start();
        Runner::init($args)->run();
        $actual = ob_get_clean();

        $this->assertSame($expects, $actual);
    }

    public function testParseCommandWillTriggerSetOptionsFromXmlIfOnlyOneArgumentIsPass()
    {
        $args    = array('./ScriptFrontend/_files/config.xml');
        $expects = array(
            'convert_info' => array(
                'input_charset'  => 'g-utf8',
                'output_charset' => 'g-ansi',
                'files' => array(
                    array('name' => 'bar'),
                ),
                'dirs' => array(
                    array(
                        'name'           => 'foo',
                        'input_charset'  => 'd-utf8',
                        'output_charset' => 'd-ansi',
                    ),
                ),
            ),
            'convertor'           => 'mbstring',
            'convert_to_strategy' => 'long_name',
            'target_location'     => 'target-location',
            'base_path'           => 'base-path',
            'verbose'             => true,
            'extensions'          => array('php', 'txt'),
        );

        $runner = Runner::init($args);

        $this->assertEquals($expects, $runner->getOptions());
    }

    public function testParseCommandWillRaiseExceptionIfLastArgumentIsNotFileOrDirectory()
    {
        $args = array('-v', 'not-a-file-or-directory');

        $this->setExpectedException('InvalidArgumentException');

        Runner::init($args);
    }

    public function testConfigFromCommandLine()
    {
        $args = array('-c', 'mbstring', '-s', 'long_name',
            '-t', 'target-location', '-b', 'base-path',
            '-i', 'input-charset', '-o', 'output-charset',
            '-v', '-e', 'php,txt,html', __FILE__);

        $expects = array(
            'convertor'           => 'mbstring',
            'convert_to_strategy' => 'long_name',
            'target_location'     => 'target-location',
            'base_path'           => 'base-path',
            'verbose'             => true,
            'extensions'           => array('php', 'txt', 'html'),
            'convert_info' => array(
                'input_charset'  => 'input-charset',
                'output_charset' => 'output-charset',
                'files' => array(
                    array('name' => __FILE__),
                ),
            ),
        );

        $runner = Runner::init($args);

        $this->assertEquals($expects, $runner->getOptions());
    }

    public function testSetOpsionsFromXmlWillRaiseExceptionIfArgumentIsNotFileOrNotExists()
    {
        $this->setExpectedException('InvalidArgumentException');

        $runner = new Runner;
        $runner->setOptionsFromXml('not-exists-file');
    }

    public function testSetOptionsFromXmlWillRaiseExceptionIfConfigFileDoseNotContainConvertInfo()
    {
        $this->setExpectedException('InvalidArgumentException');

        $runner = new Runner;
        $runner->setOptionsFromXml('./ScriptFrontend/_files/bad_config.xml');
    }

    public function testSetOption()
    {
        $runner = new Runner;
        $result = $runner->setOption('convertor', 'mbstring');

        $this->assertSame($runner, $result);
        $this->assertEquals('mbstring', $runner->getOption('convertor'));
    }

    public function testSetOptionWithNonStringNameRaiseException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $runner = new Runner;
        $runner->setOption(false, 0);
    }

    public function testGetOption()
    {
        $runner = new Runner;
        $runner->setOption('name', 'value');

        $this->assertEquals('value', $runner->getOption('name'));
    }

    public function testGetOptionWithDefaultValue()
    {
        $runner = new Runner;

        $this->assertEquals('foo', $runner->getOption('test', 'foo'));
    }

    public function testGetOptionWithNonStringNameRaiseException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $runner = new Runner;
        $runner->getOption(false);
    }

    public function testSetOpions()
    {
        $runner = new Runner;
        $result = $runner->setOptions(array('name' => 'value'));

        $this->assertSame($runner, $result);
        $this->assertEquals('value', $runner->getOption('name'));
    }

    public function testGetOptions()
    {
        $runner = new Runner;
        $runner->setOptions(array('name', 'value'));

        $this->assertEquals(array('name', 'value'), $runner->getOptions());
    }

    public function testSetUpConvertorCanRaiseExceptionIfThePlatformDoseNotSupprotTheCovnertor()
    {
        $this->setExpectedException('RuntimeException',
            'Your platform dose not support the convertor you provide or have a available convertor');

        $runner = $this->getMock('Tcc\\ScriptFrontend\\Runner',
            array('checkEnvironment'));

        $runner->expects($this->once())
               ->method('checkEnvironment')
               ->will($this->returnValue(false));

        $runner->setOption('convert_info', array('some-convert-info'));
        $runner->run();
    }

    public function testRun()
    {
        $options = array(
            'convertor'           => 'mbstring',
            'convert_to_strategy' => 'long_name',
            'convert_info'        => array('some-convert-info'),
        );

        $runner = $this->getMock('Tcc\\ScriptFrontend\\Runner',
            array('addConvertFiles', 'convert'));

        $runner->expects($this->once())
               ->method('addConvertFiles')
               ->with($this->equalTo(array('some-convert-info')));

        $runner->setOptions($options)
               ->setPrinter(new FooPrinter);

        $runner->run();
        $convertor = $runner->getConvertor();

        $this->assertInstanceOf('Tcc\\Convertor\\MbStringConvertor', $convertor);
        $this->assertEquals(strtr(getcwd(), '\\', '/'), $convertor->getTargetLocation());
        $this->assertInstanceOf('Tcc\\Convertor\\ConvertToStrategy\\LongNameConvertToStrategy',
            $convertor->getConvertToStrategy());
    }

    public function testRunWillRaiseExceptionIfNoConvertInfoOption()
    {
        $this->setExpectedException('RuntimeException');

        $runner = new Runner;
        $runner->run();
    }

    public function testSetConvertorWithStringArgument()
    {
        $convertor = 'mbstring';

        if (!in_array($convertor, get_loaded_extensions())) {
            $this->fail(
                'Can not finish this test, cause your platform dose not have mbstring extension');

            return ;
        }

        $runner = new Runner;
        $result = $runner->setConvertor($convertor);

        $this->assertSame($runner, $result);
        $this->assertInstanceOf('Tcc\\Convertor\\MbStringConvertor', $runner->getConvertor());
    }

    public function testSetConvertorWithAbstractConvertorArgument()
    {
        $mockConvertor = $this->getMockForAbstractClass('Tcc\\Convertor\\AbstractConvertor');

        $runner = new Runner;
        $result = $runner->setConvertor($mockConvertor);

        $this->assertSame($runner, $result);
        $this->assertInstanceOf('Tcc\\Convertor\\AbstractConvertor', $mockConvertor);
    }

    public function testSetConvertorWillRaiseExceptionIfArgumentIsNotStringOrAbstractConvertor()
    {
        $this->setExpectedException('InvalidArgumentException');

        $runner = new Runner;
        $runner->setConvertor(false);
    }

    public function testGetConvertorTryToSetOneIfNotSetBefore()
    {
        $convertors = ConvertorFactory::getAvailableConvertor();

        if (!$convertors) {
            $this->fail('No available convertor on your platform to finish this test');
            return ;
        }

        $convertor = array_shift($convertors);
        $runner    = new Runner;

        $this->assertInstanceOf($convertor, $runner->getConvertor());
    }

    public function testSetConvertFileContainer()
    {
        $extensions = array('php', 'txt');

        $mockContainer = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer',
            array('setConvertExtensions'));

        $mockContainer->expects($this->once())
                      ->method('setConvertExtensions')
                      ->with($this->equalTo($extensions));

        $runner = new Runner;
        $runner->setOption('extensions', $extensions);
        $result = $runner->setConvertFileContainer($mockContainer);

        $this->assertSame($runner, $result);
        $this->assertInstanceOf('Tcc\\ConvertFile\\ConvertFileContainer',
            $runner->getConvertFileContainer());
    }

    public function testGetConvertFileContainerWillReturnDefaultIfNotSetBefore()
    {
        $runner = new Runner;

        $this->assertInstanceOf('Tcc\\ConvertFile\\ConvertFileContainer',
            $runner->getConvertFileContainer());
    }

    public function testSetPrinter()
    {
        $runner  = new Runner;
        $printer = $this->getMock('Tcc\\Test\\ScriptFrontend\\TestAssert\\FooPrinter',
            array('setAppRunner'));

        $printer->expects($this->once())
                ->method('setAppRunner')
                ->with($this->identicalTo($runner));

        $result = $runner->setPrinter($printer);

        $this->assertSame($runner, $result);
        $this->assertInstanceOf('Tcc\\ScriptFrontend\\Printer\\PrinterInterface',
            $runner->getPrinter());
    }

    public function testGetPrinterWillSetConsolePrinterIfNotSetBefore()
    {
        $runner = new Runner;

        $this->assertInstanceOf('Tcc\\ScriptFrontend\\Printer\\ConsolePrinter',
            $runner->getPrinter());
    }

    public function testAddConvertFile()
    {
        $convertFile   = 'convert-file';
        $inputCharset  = 'utf-8';
        $outputCharset = 'ansi';

        $container = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer',
            array('addFile'));

        $container->expects($this->once())
                  ->method('addFile')
                  ->with($this->equalTo($convertFile),
                        $this->equalTo($inputCharset),
                        $this->equalTo($outputCharset));

        $runner = new Runner;
        $runner->setConvertFileContainer($container);

        $runner->addConvertFile($convertFile, $inputCharset, $outputCharset);
    }

    public function testAddConvertFileWillRaiseExceptionIfPassValueIsNotStringOrConvertFileInstance()
    {
        $this->setExpectedException('InvalidArgumentException',
            'Invalid convert file, type: boolean, value: false');

        $runner = new Runner;
        $runner->addConvertFile(false);
    }

    public function testAddConvertFilesWithArray()
    {
        $convertFiles = array('convert-files');

        $container = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer',
            array('addFiles'));

        $container->expects($this->once())
                  ->method('addFiles')
                  ->with($this->equalTo(new ConvertFileAggregate($convertFiles)));

        $runner = new Runner;
        $runner->setConvertFileContainer($container);

        $runner->addConvertFiles($convertFiles);
    }

    public function testAddConvertFilesWithConvertFileAggregate()
    {
        $convertFiles = new ConvertFileAggregate(array('convert-files'));

        $container = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer',
            array('addFiles'));

        $container->expects($this->once())
                  ->method('addFiles')
                  ->with($this->equalTo($convertFiles));

        $runner = new Runner;
        $runner->setConvertFileContainer($container);

        $runner->addConvertFiles($convertFiles);
    }

    public function testAddConvertFilesWillRaiseExceptionIfConvertFilesNotArrayOrConvertFileAggregate()
    {
        $this->setExpectedException('InvalidArgumentException',
            'Invalid convertFiles');

        $runner = new Runner;
        $runner->addConvertFiles('invalid-convert-files');
    }

    public function testClearConvertFile()
    {
        $container = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer',
            array('clearConvertFiles'));

        $container->expects($this->once())
                  ->method('clearConvertFiles');

        $runner = new Runner;
        $runner->setConvertFileContainer($container);

        $runner->clearConvertFiles();
    }

    public function prepareConvertor()
    {
        $runner = new Runner;

        $runner->setConvertor(new FooConvertor)
               ->setPrinter(new FooPrinter);

        return $runner;
    }

    public function testConvert()
    {
        $badConvertFile = new FooConvertFile;
        $badConvertFile->setConvertErrorFlag(true);

        $runner = $this->prepareConvertor();

        $runner->addConvertFile(new FooConvertFile)
               ->addConvertFile($badConvertFile)
               ->addConvertFile(new FooConvertFile);

        $runner->convert();

        $this->assertEquals(3, $runner->convertFileCount());
        $this->assertEquals(3, $runner->convertFileCount(Runner::COUNT_CONVERTED));
        $this->assertEquals(1, $runner->convertFileCount(Runner::COUNT_FAILURE));
        $this->assertEquals(2, $runner->convertFileCount(Runner::COUNT_SUCCESS));
        $this->assertFalse($runner->getConvertErrorFlag());
    }

    public function testSetConvertResultWillRaiseExceptionIfErrorMessageIsNotStringOrNull()
    {
        $this->setExpectedException('InvalidArgumentException');

        $runner = new Runner;
        $runner->setConvertResult(new FooConvertFile, false);
    }

    public function testGetConvertResult()
    {
        $convertFile    = new FooConvertFile;
        $badConvertFile = new FooConvertFile;
        $badConvertFile->setConvertErrorFlag(true);

        $runner = $this->prepareConvertor();

        $runner->addConvertFile($convertFile)
               ->addConvertFile($badConvertFile);

        $runner->convert();
        $resultStorage = $runner->getConvertResult();

        $this->assertTrue($runner->getConvertErrorFlag());

        $resultStorage->rewind();
        $this->assertSame($convertFile, $resultStorage->current());
        $resultStorage->next();
        $this->assertSame($badConvertFile, $resultStorage->current());
    }
}
