<?php
namespace Tcc\Convertor\ConvertToStrategy;

class LongNameConvertToStrategy implements ConvertToStrategyInterface
{
    public function getConvertToFile(AbstractConvertor $convertor)
    {
        $convertingFile = $convertor->getConvertingFile();
        $pathname       = $convertingFile->getPathname();

        $filename    = preg_replace('/^(\\/|[a-zA-Z]\\:\\/)/', '', $pathname);
        $newPathname = $this->getTargetLocation()
                  . '/' . str_replace('/', '_', $filename);

        $fileObject = new SplFileObject($newPathname, 'w');

        return $fileObject;
    }
}
