<?php
namespace Tcc\Convertor\ConvertToStrategy;

class LongNameConvertToStrategy extends AbstractConvertToStrategy
{
    public function getConvertToFile()
    {
        $convertingFile = $this->convertor->getConvertingFile();
        $filePathname   = $convertingFile->getPathname();

        $filename = $this->getTargetLocation()
                  . '/' . str_replace('/', '_', $filePathname);

        $fileObject = new SplFileObject($filename, 'w');

        return $fileObject;
    }
}
