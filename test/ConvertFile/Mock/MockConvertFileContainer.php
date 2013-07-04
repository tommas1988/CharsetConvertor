<?php
namespace Tcc\Test\ConvertFile\Mock;

use Tcc\ConvertFile\ConvertFileContainerInterface;
use Tcc\ConvertFile\ConvertFileAggregateInterface;
use Tcc\ConvertFile\ConvertFile;

class MockConvertFileContainer implements ConvertFileContainerInterface
{
    protected $convertFiles      = array();
    protected $convertExtensions = array();

    public function addFile($convertFile, $inputCharset = null, $outputCharset = null)
    {
        if ($convertFile instanceof ConvertFile) {
            $inputCharset  = $convertFile->getInputCharset();
            $outputCharset = $convertFile->getOutputCharset();
            $convertFile   = $convertFile->getPathname();
        }

        $this->convertFiles[] = array(
            'name'           => $convertFile,
            'input_charset'  => $inputCharset,
            'output_charset' => $outputCharset,
        );
    }

    public function addFiles(ConvertFileAggregateInterface $aggregate)
    {

    }

    public function getConvertFiles()
    {
        return $this->convertFiles;
    }

    public function clearConvertFiles()
    {
        $this->convertFiles = array();
    }

    public function setConvertExtensions(array $extensions)
    {
        $this->convertExtensions = $extensions;
    }

    public function getConvertExtensions()
    {
        return $this->convertExtensions;
    }

    public function count()
    {
        
    }
}
