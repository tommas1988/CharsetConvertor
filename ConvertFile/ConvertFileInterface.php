<?php
namespace Tcc\ConvertFile;

use IteratorAggregate;

interface ConvertFileInterface extends IteratorAggregate
{
    public function getFilename();
    public function getInputCharset();
    public function getOutputCharset();
    public function getPathName();
    public function getPath();
    public function getExtension();
}
