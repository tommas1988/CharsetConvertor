<?php
namespace Tcc\Convertor;

use ConvertFile\ConvertFileInterface;
use SplFileObject;
use Exception;

class MbStringConvertor extends AbstractConvertor
{
    public function getName()
    {
        return 'mbstring';
    }

    protected function doConvert()
    {
        $convertFile       = $this->getConvertFile();
        $inputCharset      = $convertFile->getInputCharset();
        $outputCharset     = $convertFile->getOutputCharset();
        $convertToStrategy = $this->getConvertToStrategy();

        set_error_handler(array($this, 'convertError'), E_WARNING);

        foreach ($convertFile as $line) {
            $convertToStrategy->convertTo(mb_convert_encoding($line, $outputCharset, $inputCharset));
        }

        restore_error_handler();
    }
}
