<?php
/**
 * CharsetConvertor
 * 
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

namespace Tcc\Convertor;

use Tcc\Convertor\AbstractConvertor;
use InvalidArgumentException;
use RuntimeException;

/**
 * Convertor factory
 */
abstract class ConvertorFactory
{
    /**
     * All available convertors.
     *
     * @var null|array
     */
    protected static $availableConvertors = null;

    /**
     * Check platform environment.
     *
     * @param  null|string\AbstractConvertor $convertor
     * @return bool
     * @throws InvalidArgumentException If convertor is not string null, or 
     *         instance of AbstractConvertor
     */
    public static function checkEnvironment($convertor = null)
    {
        if (is_string($convertor)) {
            $convertorName = strtolower($convertor);
        } elseif ($convertor instanceof AbstractConvertor) {
            $convertorName = $convertor->getName();
        } elseif (!is_null($convertor)) {
            throw new InvalidArgumentException();
        }
        
        $convertors = static::getAvailableConvertor();

        if ($convertors === false
            || isset($convertorName)
            && !in_array($convertorName, array_flip($convertors))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Factory method
     *
     * @param  null|string $convertorName
     * @return AbstractConvertor
     * @throws RuntimeException If no available convertor for the platform
     */
    public static function factory($convertorName = null)
    {
        if (!static::checkEnvironment($convertorName)) {
            throw new RuntimeException('No convertor could be used!');
        }

        $convertors    = static::getAvailableConvertor();
        $convertorName = strtolower($convertorName);

        if ($convertorName) {
            return new $convertors[$convertorName];
        }

        $convertor = current($convertors);

        return new $convertor;
    }

    /**
     * Get all available convertor.
     *
     * @return false|array
     */
    public static function getAvailableConvertor()
    {
        if (!is_null(static::$availableConvertors)) {
            return static::$availableConvertors;
        }

        $convertors = array();
        $extensions = get_loaded_extensions();
        if (in_array('mbstring', $extensions)) {
            $convertors['mbstring'] = 'Tcc\\Convertor\\MbStringConvertor';
        }

        if (in_array('iconv', $extensions)) {
            $convertors['iconv'] = 'Tcc\\Convertor\\IConvConvertor';
        }

        if (in_array('recode', $extensions)) {
            $convertors['recode'] = 'Tcc\\Convertor\\RecodeConvertor';
        }

        return static::$availableConvertors = empty($convertors) ? false : $convertors;
    }
}
