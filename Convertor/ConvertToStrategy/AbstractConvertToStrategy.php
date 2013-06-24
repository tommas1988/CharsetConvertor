<?php
namespace Tcc\Convertor\ConvertToStrategy;

class AbstractConvertToStrategy
{
    protected $convertor;

    abstract public function getConvertToFile();

    public function __construct(AbstractConvertor $convertor)
    {
        $this->convertor = $convertor;
    }
}
