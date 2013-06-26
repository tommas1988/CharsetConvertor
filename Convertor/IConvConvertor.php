<?php
namespace Tcc\Convertor;

use ConvertFile\ConvertFileInterface;
use SplFileObject;

class IConvConvertor extends AbstractConvertor
{
    public function getName()
    {
        return 'iconv';
    }
    
    protected function doConvert()
    {

    }
}
