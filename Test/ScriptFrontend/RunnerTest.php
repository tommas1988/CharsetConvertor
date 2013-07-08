<?php
namespace Tcc\Test\ScriptFrontend;

use Tcc\ScriptFrontend\Runner;
use Tcc\ScriptFrontend\Printer\ConsolePrinter;
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
}
