<?php
namespace Tcc\Convertor;

use Exception;

class ConvertorFactory
{
    public static function checkEnvrionment($convertor = null)
    {
        if (is_string($convertor)) {
            $convertorName = $convertor;
        } elseif ($convertor instanceof AbstractConvertor) {
            $convertorName = $convertor->getName();
        } else {
            throw new Exception();
        }
        
        $convertExtensions = array();
        $extensions = get_loaded_extensions();
        if (in_array('iconv', $extensions)) {
            $convertExtensions[] = 'iconv';
        }
        if (in_array('recode', $extensions)) {
            $convertExtensions[] = 'recode';
        }
        if (in_array('mbstring', $extensions)) {
            $convertExtensions[] = 'mbstring';
        }

        if ($convertor === null) {
            return count($convertExtensions) > 0;
        }

        return in_array(strtolower($convertorName), $convertExtensions);
    }

    public static function factory($convertorName = 'mbstring')
    {
        $convertorClass = static::getConvertorClass($convertorName);

        if (!$convertorClass) {
            throw new Exception('can not get a proper covertor');
        }

        return new $convertorClass;
    }

    public static function getConvertorClass($convertorName)
    {
        if (!is_string($convertorName)) {
            throw new Exception();
        }

        $convertorClassMap = array(
            'iconv'    => 'Tcc\\IConvConvertor',
            'recode'   => 'Tcc\\RecodeConvertor',
            'mbstring' => 'Tcc\\MbStringConvertor',
        );

        $convertorName = strtolower($convertorName);
        if (in_array($convertorName, $convertorClassMap)) {
            return $convertorClassMap[$convertorName];
        }

        return false;
    }
}
