<?php
namespace Tcc\Convertor\ConvertToStrategy;

class LongNameConvertToStrategy extends AbstractConvertToStrategy
{
    protected function generateTargetFileName()
    {
        $convertor   = $this->convertor;
        $convertFile = $convertor->getConvertFile();
        $pathname    = $convertFile->getPathname();
        $filename    = preg_replace('/^(\\/|[a-zA-Z]\\:\\/)/', '', $pathname);

        return $convertor->getTargetLocation() . '/' . str_replace('/', '_', $filename);
    }
}
