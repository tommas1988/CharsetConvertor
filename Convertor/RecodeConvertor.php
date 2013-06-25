<?php
namespace Tcc\Convertor;

use ConvertFile\ConvertFileInterface;
use SplFileObject;

class RecodeConvertor extends AbstractConvertor
{
    public function getName()
    {
        return 'recode';
    }

    protected function doConvert(ConvertFileInterface $convertFile,
        SplFileObject $convertToFile
    ){

    }
}
