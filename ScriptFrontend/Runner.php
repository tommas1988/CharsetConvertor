<?php
/**
 * CharsetConvertor
 * 
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

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
use SimpleXMLElement;
use InvalidArgumentException;
use RuntimeException;
use Exception;

/**
 * Application frontend.
 */
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

    /**
     * @var \Tcc\Convertor\AbstractConvertor
     */
    protected $convertor;

    /**
     * @var \Tcc\ConvertFile\ConvertFileContainer
     */
    protected $convertFileContainer;

    /**
     * @var Printer\PrinterInterface
     */
    protected $printer;

    /**
     * Convert error flag
     * @var bool
     */
    protected $convertError;

    /**
     * Mark whether application is halted.
     *
     * Application will stop to run at some situation, e.g. when using
     *  --help command
     * @var bool
     */
    protected $isHalt = false;

    /**
     * Count convert failure
     * @var int
     */
    protected $convertFailureCount = 0;

    /**
     * Array of application options
     * @var array
     */
    protected $options = array();

    /**
     * Storage of ConvertFile obejcts with its convert error message.
     * @var \SplObjectStorage
     */
    protected $resultStorage;

    /**
     * Constructor.
     *
     * Initialize resultStorage.
     */
    public function __construct()
    {
        $this->resultStorage = new SplObjectStorage;
    }

    /**
     * Initialize application.
     *
     * @param  array $args Array of command line arguments
     * @return self
     */
    public static function init(array $args)
    {
        $runner = new static;
        $runner->parseCommand($args);

        return $runner;
    }

    /**
     * Parse command line arguments into application options.
     *
     * @param  array $args Array of command line arguments
     * @throws RuntimeException If arguments is empty
     * @throws InvalidArgumentException If the last arguemnt is not file or directory
     */
    protected function parseCommand(array $args)
    {
        if (empty($args)) {
            throw new RuntimeException('No argument is provided');
        }

        $keys = array_flip($args);
        // Stop parse if meet --help command argument
        if (isset($keys['--help'])) {
            ConsolePrinter::printHelpInfo();
            $this->isHalt = true;
            return;
        }

        // Stop parse if meet --version command argument
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

    /**
     * Set application options from xml file.
     *
     * @param  string $configFile configuration file
     * @throws InvalidArgumentException If configuration file is not string or
     *         exists
     * @throws InvalidArgumentException If configuration file dose not contain
     *         a confile_info element
     */
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

    /**
     * Run the application
     *
     * @throws RuntimeException If dose not have a convert_info option
     */
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

    /**
     * Set a application option.
     *
     * @param  string $name
     * @param  mixed $value
     * @return self
     * @throws InvalidArgumentException If option name is string
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Invalid option name');
        }

        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Get a application option.
     * 
     * @param  string $name
     * @param  null|mixed $default Default option value if have not setted yet
     * @return mixed
     * @throws InvalidArgumentException If option name is not string
     */
    public function getOption($name, $default = null)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Invalid option name');
        }

        return (isset($this->options[$name])) ? $this->options[$name] : $default;
    }

    /**
     * Reset application options
     * @param  array $options
     * @return self
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get application options
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Setup convertor
     * @throws RuntimeException If the platform dose not support specific convertor
     */
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

    /**
     * Check platform environment.
     * 
     * @param  null|string|Tcc\Convertor\AbstractConvertor $convertor
     * @return bool
     */
    public function checkEnvironment($convertor = null)
    {
        return ConvertorFactory::checkEnvironment($convertor);
    }

    /**
     * Set convertor.
     * 
     * @param  string|Tcc\Convertor\AbstractConvertor $convertor
     * @return self
     * @throws InvalidArgumentException If convertor is not string or instance 
     *         of AbstractConvertor
     */
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

    /**
     * Get convertor
     * 
     * @return AbstractConvertor
     */
    public function getConvertor()
    {
        if (!$this->convertor) {
            $this->setConvertor(ConvertorFactory::factory());
        }

        return $this->convertor;
    }

    /**
     * Set ConvertFileContainer
     *
     * @param  ConvertFileContainer $container 
     * @return self
     */
    public function setConvertFileContainer(ConvertFileContainer $container)
    {
        $container->setConvertExtensions($this->getOption('extensions'));
        $this->convertFileContainer = $container;

        return $this;
    }

    /**
     * Get ConvertFileContainer
     *
     * @return Tcc\ConvertFile\ConvertFileContainer
     */
    public function getConvertFileContainer()
    {
        if (!$this->convertFileContainer) {
            $this->setConvertFileContainer(new ConvertFileContainer);
        }

        return $this->convertFileContainer;
    }

    /**
     * Set Printer
     *
     * @param  Printer\PrinterInterface $printer
     * @return self
     */
    public function setPrinter(PrinterInterface $printer)
    {
        $printer->setAppRunner($this);
        $this->printer = $printer;

        return $this;
    }

    /**
     * Get Printer
     *
     * @return Printer\PrinterInterface
     */
    public function getPrinter()
    {
        if (!$this->printer) {
            $this->setPrinter(new ConsolePrinter);
        }

        return $this->printer;
    }

    /**
     * Add a convert file
     *
     * @param  string|Tcc\ConvertFile\ConvertFile $convertFile
     * @param  null|string $inputCharset
     * @param  null|string $outputCharset
     * @return self
     * @throws InvalidArgumentException If convert file argument is not string or 
     *         instance of ConvertFile
     */
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

    /**
     * Add convert files
     *
     * @param  array|Tcc\ConvertFile\ConvertFileAggregate $convertFiles
     * @return self
     * @throws InvalidArgumentException If covnertFiles is not array or instance 
     *         of ConvertFileAggregate
     */
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

    /**
     * Empty ConvertFiles.
     *
     * @return self
     */
    public function clearConvertFiles()
    {
        $container = $this->getConvertFileContainer();
        $container->clearConvertFiles();

        return $this;
    }

    /**
     * Convert action
     */
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

    /**
     * Get covnert error flag
     *
     * @return bool
     */
    public function getConvertErrorFlag()
    {
        return $this->convertError;
    }

    /**
     * Count convert file in different ways
     *
     * @param  int $flag
     * @return int
     * @throws InvalidArgumentException If count flag is not defined
     */
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

    /**
     * Set covnert result
     *
     * @param  Tcc\ConvertFile\ConvertFile $convertFile
     * @param  null|string $errMsg
     * @return self
     * @throws InvalicArgumentException If error message is not string
     */
    public function setConvertResult(ConvertFile $convertFile, $errMsg = null)
    {
        if (!is_string($errMsg) && $errMsg !== null) {
            throw new InvalidArgumentException(sprintf(
                'Invalid error message type: %s', gettype($errMsg)));
        }

        $this->resultStorage->attach($convertFile, $errMsg);
        return $this;
    }

    /**
     * Get convert result
     *
     * @return SplObjectStorage
     */
    public function getConvertResult()
    {
        return $this->resultStorage;
    }
}
