<?php
namespace Tcc\ScriptFrontend;

use Tcc\Convertor\AbstractConvertor;
use Tcc\Convertor\ConvertorFactory;
use Tcc\ConvertFile\ConvertFileContainerInterface;
use Tcc\ConvertFile\ConvertFileContainer;
use Tcc\Resolver\ResolverUtils as Resolver;
use Tcc\ScriptFrontend\Printer\PrinterInterface;
use Tcc\ScriptFrontend\Printer\ConsolePrinter;
use SplObjectStorage;
use SimpleXmlElement;
use InvalidArgumentException;
use RuntimeException;

class Runner
{
    const PRE_CONVERT  = 0;
    const CONVERTING   = 1;
    const CONVERT_POST = 2;

    const COUNT_ALL       = 0;
    const COUNT_CONVERTED = 1;
    const COUNT_FAILURE   = 2;
    const COUNT_SUCCESS   = 3;

    const VERSION = '1.0.0-alpha';

    protected $convertor;
    protected $convertFileContainer;
    protected $printer;
    protected $convertError;
    protected $isHalt = false;
    protected $convertFailureCount = 0;
    protected $options = array();
    protected $resultStorage;

    public function __construct()
    {
        $this->resultStorage = new SplObjectStorage;
    }

    public static function init(array $args)
    {
        $runner = new static;
        $runner->parseCommand($args);

        return $runner;
    }

    protected function parseCommand(array $args)
    {
        if (empty($args)) {
            throw new RuntimeException('No argument is provided');
        }

        $keys = array_keys($args);
        if (isset($keys['--help'])) {
            ConsolePrinter::printHelpInfo();
            $this->isHalt = true;

            return;
        }

        if (isset($keys['--version'])) {
            ConsolePrinter::printVersion();
            $this->isHalt = true;

            return;
        }

        $count = count($args);
        if ($count === 1) {
            $this->setOptionsFromXml($args[0]);
            return;
        }

        $filename    = array_pop($args);
        $convertInfo = array();
        if (is_dir($filename)) {
            $convertInfo['dirs'][] = array('name' => $filename);
        } elseif (is_file($filename)) {
            $convertInfo['files'][] = array('name' => $filename);
        } else {
            throw new InvalidArgumentException('Invalid convert element');
        }

        for ($i = 0; $i < $count; $i += 2) {
            $arg = $args[$i];
            $var = isset($args[$i + 1]) ? $args[$i + 1] : null;

            if ($arg === '--convertor' || $arg === '-c') {
                $this->setOption('convertor', $val);
                break;
            } elseif ($arg === '--convert-to-strategy' || $arg === '-s') {
                $this->setOption('convert_to_strategy', $val);
                break;
            } elseif ($arg === '--target-location' || $arg === '-t') {
                $this->setOption('target_location', $val);
                break;
            } elseif ($arg === '--base-path' || $arg === '-b') {
                $this->setOption('base_path', $val);
                break;
            } elseif ($arg === '--input-charset' || $arg === '-i') {
                $convertInfo['input_charset'] = $val;
                break;
            } elseif ($arg === '--output-charset' || $arg === '-o') {
                $convertInfo['output_charset'] = $val;
                break;
            } elseif ($arg === '--verbose' || $arg === '-v') {
                $this->setOption('verbose', true);
                $i--;
                break;
            } elseif ($arg === '--extension' || $arg === '-e') {
                $this->setOption('extensions', explode(',', $val));
                break;
            } else {
                ConsolePrinter::printUndefinedCommand($arg);
                $this->ishalt = true;
                return ;
            }
        }

        $this->setOption('convert_info', $convertInfo);
    }

    public function setOptionsFromXml($configFile)
    {
        if (!is_string($configFile) || !file_exists($configFile)) {
            throw new InvalidArgumentException('Invalid config file');
        }

        $config = new SimpleXMLElement($configFile, 0, true);

        if (isset($config->convert_info)) {
            throw new InvalidArgumentException('The needed convert_info field is not provided');
        }

        $this->setOption(
            'convert_info',
            Resolver::resolveConvertInfoFromXml($config->convert_info));

        $optionNames = array(
            'converor', 
            'convert_to_strategy',
            'target_location',
            'base_path',
        );

        foreach ($optionNames as $optionName) {
            if (isset($config->$optionName)) {
                $this->setOption($optionName, (string) $config->$optionName);
            }
        }

        if (isset($config->verbose)) {
            $this->setOption('verbose', (bool) (string) $config->verbose);
        }

        if (isset($config->extensions)) {
            $extensions = array();
            foreach ($config->extensions->extension as $extension) {
                $extensions[] = (string) $extension;
            }
            $this->setOption('extensions', $extensions);
        }
    }

    public function run()
    {
        if ($this->isHalt) {
            return ;
        }

        if ($this->getOption('convert_info')) {
            throw new RuntimeException('You haven`t given the converted files');
        }

        $this->setUpConvertor();

        $this->addConvertFiles($this->getOption('convert_info'));

        $printer = $this->getPrinter();
        $printer->update(static::PRE_CONVERT);

        $this->convert();

        $printer->update(static::CONVERT_POST);
    }

    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Invalid option name');
        }

        $this->options[$name] = $value;
        return $this;
    }

    public function getOption($name, $default = null)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Invalid option name');
        }

        return (isset($this->options[$name])) ? $this->options[$name] : $default;
    }

    public function setOptions(array $options)
    {
        $this->setOptions = $options;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    protected function setUpConvertor()
    {
        $convertor = $this->getOption('convertor');
        if (!$this->checkEnvironment($convertor)) {
            throw new RuntimeException(
                'Your platform dose not support the convertor you provide or have a available convertor'
            );
        }

        if ($convertor) {
            $this->setConvertor($convertor);
        }

        $targetLocation = $this->getOption('target_location', getcwd());
        $this->getConvertor()->setTargetLocation($targetLocation);

        $convertToStrategy = $this->getOption('convert_to_strategy');
        if ($convertToStrategy
            && Resolver::resolveClassName(
                'convertor_convert_to_strategy',
                $convertToStrategy . '_convert_to_strategy')
        ) {
            $strategy = new $convertToStrategy;
            if ($convertToStrategy === 'mirror') {
                $strategy->setBasePath($this->getOption('base_path'), '');
            }

            $this->getConvertor()->setConvertToStrategy($strategy);
        }
    }

    public function checkEnvironment($convertor = null)
    {
        return ConvertorFactory::checkEnvironment($convertor);
    }

    public function setConvertor($convertor)
    {
        if (is_string($convertor)) {
            $this->convertor = ConvertorFactory::factory($convertor);
        } elseif ($convertor instanceof AbstractConvertor) {
            $this->convertor = $convertor;
        } else {
            throw new InvalidArgumentException('Invalid convertor');
        }

        return $this;
    }

    public function getConvertor()
    {
        if (!$this->convertor) {
            $this->setConvertor(ConvertorFactory::factory());
        }

        return $this->convertor;
    }

    public function setConvertFileContainer(ConvertFileContainerInterface $container)
    {
        $container->setConvertExtensions($this->getOption('extensions'));
        $this->convertFileContainer = $container;

        return $this;
    }

    public function getConvertFileContainer()
    {
        if (!$this->convertFileContainer) {
            $this->setConvertFileContainer(new ConvertFileContainer);
        }

        return $this->convertFileContainer;
    }

    public function setPrinter(PrinterInterface $printer)
    {
        $printer->setAppRunner($this);
        $this->printer = $printer;

        return $this;
    }

    public function getPrinter()
    {
        if (!$this->printer) {
            $this->setPrinter(new ConsolePrinter);
        }

        return $this->printer;
    }

    public function addConvertFile($convertFile, $inputCharset, $outputCharset)
    {
        $container = $this->getConvertFileContainer();
        $container->addFile($convertFile, $inputCharset, $outputCharset);

        return $this;
    }

    public function addConvertFiles(array $convertFiles)
    {
        $container = $this->getConvertFileContainer();
        $container->addFiles($convertFiles);

        return $this;
    }

    public function clearConvertFiles()
    {
        $container = $this->getConvertFileContainer();
        $container->clearConvertFiles();

        return $this;
    }

    public function convert()
    {
        $convertFiles = $this->getConvertFileContainer()->getConvertFiles();

        foreach ($convertFiles as $convertFile) {
            //reset convert error flag
            $this->convertError = false;
            $errMsg = null;

            try {
                $this->convertor->convert($convertFile);
            } catch (Exception $e) {
                //set convert error flag
                $this->convertError = true;
                $this->convertFailureCount++;
                $errMsg = $e->getMessage();
            }

            $this->setConvertResult($convertFile, $errMsg);

            $this->getPrinter()->update(static::CONVERTING);
        }
    }

    public function getConvertErrorFlag()
    {
        return $this->convertError;
    }

    public function convertFileCount($flag = 0)
    {
        switch ($flag) {
            case static::COUNT_ALL:
                return $this->getConvertFileContainer()->count();
            case static::COUNT_CONVERTED:
                return count($this->resultStorage);
            case static::COUNT_FAILURE:
                return $this->convertFailureCount;
            case static::COUNT_SUCCESS:
                return count($this->resultStorage) - $this->convertFailureCount;
            default :
                throw new InvalidArgumentException('Invalid count flag');
        }
    }

    public function setConvertResult(ConvertFileInterface $convertFile, $errMsg = null)
    {
        if (!is_string($errMsg) || $errMsg !== null) {
            throw new InvalidArgumentException(
                'Invalid error message type: ' . gettype($errMsg));
        }

        $this->resultStorage->attach($convertFile, $errMsg);

        return $this;
    }

    public function getConvertResult()
    {
        return $this->resultStorage;
    }
}
