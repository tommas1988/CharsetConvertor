<?php
namespace Tcc\Convertor;

use ConvertFile\ConvertFileInterface;

class IConvConvertor extends AbstractConvertor
{
    public function getName()
    {
        return 'iconv';
    }
    
    public function convert(ConvertFileInterface $convertFile)
    {

    }
}
