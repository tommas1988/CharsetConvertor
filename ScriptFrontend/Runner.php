<?php
namespace Tcc\ScriptFrontend;

use Tcc\Convertor\AbstractConvertor;
use Tcc\Convertor\ConvertorFactory;
use Tcc\Resolver\ResolverUtils as Resolver;
use Exception;

class Runner
{
    const PRE_CONVERT  = 0;
    const CONVERTING   = 1;
    const CONVERT_POST = 2;

    protected $convertor;
    protected $convertFileContainer;
    protected $options = array();

    public static function init()
    {
        $args = (isset($argv)) ? $argv ? $_SERVER['argv'];
        $args = array_shift($args);

        $runner = new static;
        $runner->parseCommand($args);

        return $runner;
    }

    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Invalid option name');
        }

        $this->options[$name] = $value;
    }

    public function getOption($name, $default = null)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Invalid option name');
        }

        return (isset($this->options[$name])) ? $this->options[$name] : $default;
    }

    protected function parseCommand(array $args)
    {
        if (empty($args)) {
            throw new RuntimeException('No argument is provided');
        }

        $keys = array_keys($args);
        if (isset($keys['--help'])) {
            $this->showHelpMessage();
            return;
        }

        if (isset($keys['--version'])) {
            $this->showVersion();
            return;
        }

        $count = count($args);

        if ($count === 1) {
            $this->setOptionsFromXml($args[0]);
            return;
        }

        $filename = array_pop($args);
        $convertInfo = array();
        if (is_dir($filename)) {
            $convertInfo['dirs'][] = array('name' => $filename);
        } elseif (is_file($filename)) {
            $convertInfo['file'][] = array('name' => $filename);
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
            } elseif ($arg === '--target-location' || $arg === 't') {
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
            'verbose',
        );

        foreach ($optionNames as $optionName) {
            if (isset($config->$optionName)) {
                $this->setOption($optionName, $config->$optionName);
            }
        }
    }

    public function run()
    {
        if ($this->getOption('convert_info')) {
            throw new RuntimeException('You haven`t given the converted files');
        }

        $this->setUpConvertor();

        $this->addConvertFiles($this->getOption('convert_info'));

        //show app header or other info

        $this->convert();

        //show result operation
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
            && Resolver::resolveClassName('convertor_convert_to_strategy', $convertToStrategy, 'convert_to_strategy')
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
        return Convertor::checkEnvironment($convertor);
    }

    public function setConvertor($convertor)
    {
        if (is_string($convertor)) {
            if (!$convertorClass = ConvertorFactory::getConvertorClass($convertor)) {
                throw new Exception();
            }
            $this->convertor = new $convertorClass;
        } elseif ($convertor instanceof AbstractConvertor) {
            $this->convertor = $convertor;
        } else {
            throw new Exception();
        }
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
        $this->convertFileContainer = $container;
    }

    public function getConvertFileContainer()
    {
        if (!$this->convertFileContainer) {
            $this->setConvertFileContainer(new ConvertFileContainer);
        }

        return $this->convertFileContainer;
    }

    public function addConvertFile($convertFile, $inputCharset, $outputCharset)
    {
        $container = $this->getConvertFileContainer();
        $container->addFile($convertFile, $inputCharset, $outputCharset);
    }

    public function addConvertFiles(array $convertFiles)
    {
        $container = $this->getConvertFileContainer();
        $container->addFiles($convertFiles);
    }

    public function clearConvertFiles()
    {
        $container = $this->getConvertFileContainer();
        $container->clearConvertFiles();
    }

    public function convert()
    {
        $convertFiles = $this->container->getConvertFiles();

        foreach ($convertFiles as $convertFile) {
            try {
                $this->convertor->convert($convertFile);
                $this->convertedFiles[] = $convertFile->getPathname();
            } catch (Exception $e) {
                
            }
        }
    }

    public function getConvertResult()
    {

    }

    public function showHelpMessage()
    {

    }

    public function showVersion()
    {

    }
}
