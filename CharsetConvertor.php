<?php
namespace Tcc;

use Tcc\Convertor\AbstractConvertor;
use Tcc\Convertor\ConvertorFactory;
use Exception;

class CharsetConvertor
{
    protected $convertor;
    protected $convertFileContainer;
    protected $convertedFiles = array();

    public function __construct(ConvertFileContainerInterface $container = null)
    {
        if (!$this->checkEnvironment) {
            throw new \Exception();
        }

        if ($container) {
            $this->setConvertFileContainer($container);
        }
    }

    public function checkEnvironment()
    {
        return Convertor::checkEnvironment();
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
}
