<?php
namespace Tcc\Test\ScriptFrontend;

use Tcc\ScriptFrontend\Runner;
use Tcc\ScriptFrontend\Printer\ConsolePrinter;
use Tcc\Convertor\ConvertorFactory;
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
        $args    = array('undefined-command', 'foo');
        $expects = 'Unrecognize command: undefined-command';

        ob_start();
        Runner::init($args)->run();
        $actual = ob_get_clean();

        $this->assertSame($expects, $actual);
    }

    public function testParseCommandWillTrggerSetOptionsFromXmlIfOnlyOneArgumentIsPass()
    {
        $args    = array('./ScriptFrontend/_files/config.xml');
        $expects = array(
            'convertor'           => 'mbstring',
            'convert_to_strategy' => 'long_name',
            'target_locaiton'     => 'target-lovation',
            'base_path'           => 'base-path',
            'verbose'             => true,
            'extesions'           => array('php', 'txt'),
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
        );

        Runner::init($args);

        $this->assertEquals($expects, $runner->getOptions());
    }

    public function testParseCommandWillRaiseExceptionIfLastArgumentIsNotFileOrDirectory()
    {
        $args = array('-v', 'not-a-file-or-directory');

        $this->setEpectedException('InvalidArgumentException');

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
            'target_locaiton'     => 'target-lovation',
            'base_path'           => 'base-path',
            'verbose'             => true,
            'extesions'           => array('php', 'txt', 'html'),
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

        $this->assertEquals('foo', $runner->getOption('test', 'foo'))
    }

    public function testGetOptionWithNonStringNameRaiseException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $runner = new Runner;
        $runner->getOption('false');
    }

    public function testSetOpions()
    {
        $runner = new Runner;
        $result = $runner->setOptions(array('name', 'value'));

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
        $runner = $this->getMock('Tcc\\ScriptFrontend\\Runner');

        $runner->expects($this->once())
               ->method('checkEnvironment')
               ->will($this->returnValue(false));

        $runner->setUpConvertor;
    }

    public function testSetUpConvertor()
    {
        $options = array(
            'convertor'           => 'mbstring',
            'convert_to_strategy' => 'long_name',
        );

        $runner = new Runner;
        $runner->setOptions($options);

        $runner->setUpConvertor();
        $convertor = $runner->getConvertor();

        $this->assertInstanceOf('Tcc\\Convertor\\MbStringConvertor', $convertor);
        $this->assertEquals(getcwd(), $convertor->getTargetLocation());
        $this->assertInstanceOf('Tcc\\Convertor\\ConvertToStrategy\\LongNameConvertToStrategy',
            $convertor->getConvertToStrategy);
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
        $mockConvertor = $this->getMockForAbstract('Tcc\\Convertor\\AbstractConvertor');

        $runner = new Runner;
        $result = $runner->setConvertor($mockConvertor);

        $this->assertSame($runner, $result);
        $this->assertInstanceOf('Tcc\\Convertor\\AbstractConvertor', $mockConvertor);
    }

    public function testSetConvertorWillRaiseExceptionIfArgumentIsNotStringOrAbstractConvertor()
    {
        $this->setExpectedException('InvalidArgumentExceprion');

        $runner = new Runner;
        $runner->setConvertor(false);
    }

    public function testGetConvertorTryToSetOneIfConvertorNotSetBefore()
    {
        $convertors = ConvertorFactory::getAvailableConvertor();

        if (!$convertors) {
            $this->fail('No available convertor on your platform to finish this test');
            return ;
        }

        $convertor = array_pop($convertors);
        $runner    = new Runner;

        $this->assertInstanceOf($convertor, $runner->getConvertor());
    }

    public function testSetConvertFileContainer()
    {
        $extensions = array('php', 'txt');

        $mockContainer = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer');

        $mockContainer->expects($this->once())
                      ->method('setConvertExtensions')
                      ->with($this->EqualTo($extensions));

        $runner = new Runner;
        $runner->setOption('extensions', $extensions);
        $result = $runner->setConvertFileContainer($mockContainer);

        $this->assertSame($runner, $result);
        $this->assertInstanceOf('Tcc\\ConvertFile\\ConvertFileContainerInterface',
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
        $printer = $this->getMock('Tcc\\Test\\ScriptFrontend\\TestAssert\\FooPrinter');

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

        $container = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer');

        $container->expects($this->once())
                  ->method('addFile')
                  ->with($this->EqualTo($convertFile),
                        $this->EqualTo($inputCharset)
                        $this->EqualTo($outputCharset));

        $runner = new Runner;
        $runner->setConvertFileContainer($container);

        $runner->addConvertFile($convertFile, $inputCharset, $outputCharset);
    }

    public function testAddConvertFiles()
    {
        $convertFiles = array('convert-files');

        $container = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer');

        $container->expects($this->once())
                  ->method('addFiles')
                  ->with($this->EqualTo($convertFiles));

        $runner = new Runner;
        $runner->setConvertFileContainer($container);

        $runner->addConvertFiles($convertFiles);
    }

    public function testClearConvertFile()
    {
        $container = $this->getMock('Tcc\\ConvertFile\\ConvertFileContainer');

        $container->expects($this->once())
                  ->method('clearConvertFiles')

        $runner = new Runner;
        $runner->setConvertFileContainer($container);

        $runner->clearConvertFiles();
    }

    public function testConvert()
    {
        
    }
}
