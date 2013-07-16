<?php
namespace Tcc\Convertor\ConvertToStrategy;

use Tcc\Convertor\AbstractConvertor;
use Tcc\ConvertFile\ConvertFile;
use RuntimeException;

class LongNameConvertToStrategy extends AbstractConvertToStrategy
{
    public function generateTargetFileName()
    {
        $convertor   = $this->convertor;
        $convertFile = $convertor->getConvertFile();

        if (!$convertFile instanceof ConvertFile) {
            throw new RuntimeException('Invalid convertFile');
        }

        $transArr = array('\\' => '_', '/' => '_');
        $pathname = strtr($convertFile->getPathname(), $transArr);

        $filename = preg_replace('/^(\\_|[a-zA-Z]\\:\\_)/', '', $pathname);

        return $convertor->getTargetLocation() . '/' . $filename;
    }
}
