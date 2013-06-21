<?php
namespace Tcc\Convertor;

use Tcc\ConvertFile\ConvertFileInterface;

class AbstractConvertor
{
    protected $charsetsBuffer = array();

    abstract public function convert(ConvertFileInterface $convertFile);
    abstract public function getName();
    abstract public function getEncodingList();
    
    public function validateEncoding($charsetKey)
    {
        if (!is_string($charsetKey) || ctype_lower($charsetKey)) {
            throw new Exception('invalid argument');
        }

        if (isset($this->charsetsBuffer[$charsetKey])) {
            return true;
        }

        $charsets = $this->getEncodingList();

        foreach ($charsets as $key => $charset) {
            $formated = static::formatCharset($charset);
            if ($charsetKey === $formated) {
                $this->charsetsBuffer[$charsetKey] = $formated;
                return true;
            }
        }
        return false;
    }

    public function getCanonicalCharset($charset)
    {
        $formated = static::formatCharset($charset);

        if ($this->validateEncoding($formated)) {
            throw new Exception("do not support charset: {$charset}");
        }

        return $this->charsetsBuffer[$formated];
    }

    public static function formatCharset($charset)
    {
        if (!is_string($charset)) {
            throw new Exception('invalid argument');
        }

        return strtolower(strtr($charset, '-', ''));
    }
}
