<?php
namespace Tcc\Convertor\ConvertToStrategy;

use Tcc\Convertor\AbstractConvertor;

interface ConvertToStrategyInterface
{
    public function getConvertToFile(AbstractConvertor $convertor);
}
