<?php
namespace Tcc\Test\Convertor\Mock;

use Tcc\ConvertFile\ConvertFileInterface;

class MockConvertFile implements ConvertFileInterface
{
    protected $inputCharset;
    protected $outputCharset;
    protected $iterator;

    public function getFilename()
    {

    }

    public function setInputCharset($charset)
    {
        $this->inputCharset = $charset;
    }

    public function getInputCharset()
    {
        return $this->inputCharset;
    }

    public function setOutputCharset($charset)
    {
        $this->outputCharset = $charset;
    }

    public function getOutputCharset()
    {
        return $this->outputCharset;
    }

    public function getPathname()
    {

    }

    public function getPath()
    {
        
    }

    public function getExtension()
    {

    }

    public function setIterator($iterator)
    {
        $this->iterator = $iterator;
    }

    public function getIterator()
    {
        return $this->iterator;
    }
}
