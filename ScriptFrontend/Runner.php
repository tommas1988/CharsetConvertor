<?php
namespace Tcc\ScriptFrontend;

use Tcc\Convertor\AbstractConvertor;
use Tcc\Convertor\ConvertorFactory;
use Exception;

class Runner
{
    protected $convertor;
    protected $convertFileContainer;
    protected $convertedFiles = array();
    protected $targetLocation;

    public function __construct(array $options)
    {
        $this->parseCommand($args);
    }

    public static function init()
    {
        $args = (isset($argv)) ? $argv ? $_SERVER['argv'];
        $args = array_shift($args);

        return new static($args);
    }

    public function parseCommand(array $args)
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
            $this->getOptionsFromXml($args[0]);
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

        $this->options['convert_info'] = $convertInfo;

        for ($i = 0; $i < $count; $i += 2) {
            $arg = $args[$i];
            $var = $args[$i + 1];

            if ($arg === '--convertor' || $arg === '-c') {
                $this->options['convertor'] = $val;
                break;
            } elseif ($arg === '--convert-to-strategy' || $arg === '-s') {
                $this->options['convert_to_strategy'] = $val;
                break;
            } elseif ($arg === '--target-location' || $arg === 't') {
                $this->options['target_location'] = $val;
                break;
            } elseif ($arg === '--base-path' || $arg === 'b') {
                $this->options['base_path'] = $val;
                break;
            } elseif ($arg === '--input-charset' || $arg === 'i') {
                $this->options['convert_info']['input_charset'] = $val;
                break;
            } elseif ($arg === '--output-charset' || $arg === 'o') {
                $this->options['convert_info']['output_charset'] = $val;
                break;
            } elseif ($arg === '--verbose' || $arg === 'v') {
                $this->options['verbose'] = true;
                $i--;
                break;
            }
        }
    }

    public function showHelpMessage()
    {

    }

    public function showVersion()
    {

    }

    public function getOptionsFromXml($configFile)
    {
        if (!is_string($configFile) || !file_exists($configFile)) {
            throw new InvalidArgumentException('Invalid config file');
        }

        $config = new SimpleXMLElement($configFile, 0, true);

        if (!isset($config->convert_info)) {
            throw new InvalidArgumentException('The needed convert_info field is not provided');
        }

        $this->options['convert_info'] = $this->resolveConvertInfoFromXml($config->convert_info);

        $optionNames = array(
            'converor', 
            'convert_to_strategy',
            'target_location',
            'base_path',
            'verbose',
        );

        foreach ($optionNames as $optionName) {
            if (isset($config->$optionName)) {
                $this->options[$optionName] = $config->$optionName;
            }
        }
    }

    protected function resolveConvertInfoFromXML(SimpleXMLElement $convertInfo)
    {
        //simply reduce the redundant code
        $getCommonInfo = function (SimpleXMLElement $info) {
            if (!isset($info->name)) {
                throw new InvalidArgumentException('The needed name field is not provided');
            }

            $result = array();

            $result['name'] = $info->name;

            if (isset($info->input_charset)) {
                $result['input_charset'] = $info->input_charset;
            }

            if (isset($info->output_charset)) {
                $result['output_charset'] = $info->output_charset;
            }

            return $result;
        }

        $result = array();

        if (isset($convertInfo->input_charset)) {
            $result['input_charset'] = $convertInfo->input_charset;
        }

        if (isset($convertInfo->output_charset)) {
            $result['output_charset'] = $convertInfo->output_charset;
        }

        if (isset($convertInfo->files)) {
            $result['files'] = array();
            foreach ($convertInfo->files->file as $convertFileInfo) {
                $result['files'][] = $getCommonInfo($convertFileInfo);
            }
        }

        if (isset($convertInfo->dirs)) {
            $result['dirs'] = array();
            $count = 0;
            foreach ($convertInfo->dirs->dir as $convertDirInfo) {
                $result['dirs'][$count] = $getCommonInfo($convertDirInfo);
                if (isset($convertDirInfo->subdirs)) {
                    $result['dirs'][$count]['subdirs'] = array();
                    foreach ($convertDirInfo->subdirs->subdir as $subDirInfo) {
                        $result['dirs'][$count]['subdirs'][] = $this->resolveConvertInfoFromXML($subdirInfo);
                    }
                }
            }
        }

        return $result;
    }

    public function run()
    {
        $options = $this->getOptions;


        //do some setup;

        $this->convert();
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

        $this->convertor->setTargetLocation($this->targetLocation);
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
}
