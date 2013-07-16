<?php
namespace Tcc\Convertor;

class MbStringConvertor extends AbstractConvertor
{
    public function getName()
    {
        return 'mbstring';
    }

    public function convertErrorHandler()
    {
        $this->convertError();
    }

    protected function doConvert()
    {
        $convertFile       = $this->convertFile;
        $inputCharset      = $convertFile->getInputCharset();
        $outputCharset     = $convertFile->getOutputCharset();
        $convertToStrategy = $this->getConvertToStrategy();

        set_error_handler(array($this, 'convertErrorHandler'), E_WARNING);

        foreach ($convertFile as $line) {
            $convertToStrategy->convertTo(
                mb_convert_encoding($line, $outputCharset, $inputCharset));
        }

        restore_error_handler();
    }
}
