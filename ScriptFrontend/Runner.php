<?php
namespace Tcc\ScriptFrontend;

use Tcc\Convertor\AbstractConvertor;
use Tcc\Convertor\ConvertorFactory;
use Tcc\ConvertFile\ConvertFileContainer;
use Tcc\ConvertFile\ConvertFile;
use Tcc\ConvertFile\ConvertFileAggregate;
use Tcc\Resolver\ResolverUtils as Resolver;
use Tcc\ScriptFrontend\Printer\PrinterInterface;
use Tcc\ScriptFrontend\Printer\ConsolePrinter;
use SplObjectStorage;
use InvalidArgumentException;
use RuntimeException;
use Exception;

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

        $keys = array_flip($args);
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

        $count = $count - 1;
        for ($i = 0; $i < $count; $i += 2) {
            $arg = $args[$i];
            $val = isset($args[$i + 1]) ? $args[$i + 1] : null;

            switch ($arg) {
                case '--convertor':
                case '-c':
                    $this->setOption('convertor', $val);
                    break;
                case '--convert-to-strategy':
                case '-s':
                    $this->setOption('convert_to_strategy', $val);
                    break;
                case '--target-location':
                case '-t':
                    $this->setOption('target_location', $val);
                    break;
                case '--base-path':
                case '-b':
                    $this->setOption('base_path', $val);
                    break;
                case '--input-charset':
                case '-i':
                    $convertInfo['input_charset'] = $val;
                    break;
                case '--output-charset':
                case '-o':
                    $convertInfo['output_charset'] = $val;
                    break;
                case '--verbose':
                case '-v':
                    $this->setOption('verbose', true);
                    $i--;
                    break;
                case '--extension':
                case '-e':
                    $this->setOption('extensions', explode(',', $val));
                    break;
                default:
                    ConsolePrinter::printUndefinedCommand($arg);
                    $this->isHalt = true;
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

        if (!isset($config->convert_info)) {
            throw new InvalidArgumentException('The needed convert_info field is not provided');
        }

        $this->setOption(
            'convert_info',
            Resolver::resolveConvertInfoFromXml($config->convert_info));

        $optionNames = array(
            'convertor', 
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

        if (!$this->getOption('convert_info')) {
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
        $this->options = $options;
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

        $convertToStrategy = Resolver::resolveClassName(
            'convertor_convert_to_strategy',
            $this->getOption('convert_to_strategy') . '_convert_to_strategy');

        if ($convertToStrategy) {
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

    public function setConvertFileContainer(ConvertFileContainer $container)
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

    public function addConvertFile($convertFile,
        $inputCharset = null, $outputCharset = null
    ) {
        if (!is_string($convertFile)
            && !$convertFile instanceof ConvertFile
        ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid convert file, type: %s, value: %s',
                gettype($convertFile), var_export($convertFile, true)));
        }

        $container = $this->getConvertFileContainer();
        $container->addFile($convertFile, $inputCharset, $outputCharset);

        return $this;
    }

    public function addConvertFiles($convertFiles)
    {
        if (is_array($convertFiles)) {
            $convertFiles = new ConvertFileAggregate($convertFiles);
        } elseif (!$convertFiles instanceof ConvertFileAggregate) {
            throw new InvalidArgumentException('Invalid convertFiles');
        }

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
        $convertFiles = $this->getConvertFileContainer()->getFiles();

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

    public function convertFileCount($flag = Runner::COUNT_ALL)
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

    public function setConvertResult(ConvertFile $convertFile, $errMsg = null)
    {
        if (!is_string($errMsg) && $errMsg !== null) {
            throw new InvalidArgumentException(sprintf(
                'Invalid error message type: %s', gettype($errMsg)));
        }

        $this->resultStorage->attach($convertFile, $errMsg);
        return $this;
    }

    public function getConvertResult()
    {
        return $this->resultStorage;
    }
}
