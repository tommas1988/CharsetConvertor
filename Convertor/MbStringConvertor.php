<?php
namespace Tcc\Convertor;

use ConvertFile\ConvertFileInterface;
use Exception;

class MbStringConvertor extends AbstractConvertor
{
    protected $encodingList;

    public function getName()
    {
        return 'mbstring';
    }

    public function convert(ConvertFileInterface $convertFile)
    {
        $this->convertingFile = $convertFile;

        $inputCharset  = $convertFile->getInputCharset();
        $outputCharset = $convertFile->getOutputCharset();
        $targetFile    = $this->getConvertToFile();

        $this->setErrorHandler();
        foreach ($convertFile as $line) {
            $targetFile->fwrite(mb_convert_encoding($line, $outputCharset, $inputCharset));
        }
        restore_error_handler();
    }

    protected setErrorHandler()
    {
        $convertor = $this;

        set_error_handler(function ($errno, $errstr) use ($convertor){
            throw new ErrorException($errstr, $errno);
        }, E_WARNING);
    }
}
