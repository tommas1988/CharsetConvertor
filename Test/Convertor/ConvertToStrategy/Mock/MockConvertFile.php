<?php
namespace Tcc\Test\Convertor\ConvertToStrategy\Mock;

use Tcc\ConvertFile\ConvertFileInterface;

class MockConvertFile implements ConvertFileInterface
{
    protected $pathname;

    public function getFilename()
    {

    }

    public function getInputCharset()
    {

    }

    public function getOutputCharset()
    {

    }

    public function getExtension()
    {

    }

    public function getIterator()
    {
        
    }

    public function getPathname()
    {
        return $this->pathname;
    }

    public function setPathname($pathname)
    {
        $this->pathname = $pathname;
    }
}
