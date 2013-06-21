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

    public function getEncodingList()
    {
        if (!$this->encodingList) {
            $this->encodingList = mb_list_encodings();
        }

        return $this->encodingList;
    }

    public function convert(ConvertFileInterface $convertFile)
    {
        $inputCharset  = $this->getCanonicalCharset($convertFile->getInputCharset());
        $outputCharset = $this->getCanonicalCharset($convertFile->getOutputCharset());

        foreach ($convertFile as $line) {

        }
    }
}
