<?php
namespace Tcc\Convertor\ConvertToStrategy;

class LongNameConvertToStrategy extends AbstractConvertToStrategy
{
    public function getConvertToFile()
    {
        $convertingFile = $this->convertor->getConvertingFile();
        $pathname       = $convertingFile->getPathname();

        $filename    = preg_replace('/^(\\/|[a-zA-Z]\\:\\/)/', '', $pathname);
        $newPathname = $this->getTargetLocation()
                  . '/' . str_replace('/', '_', $filename);

        $fileObject = new SplFileObject($newPathname, 'w');

        return $fileObject;
    }
}
