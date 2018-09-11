<?php
/**
 * CharsetConvertor
 * 
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

namespace Tcc\Resolver;

use SimpleXMLElement;
use InvalidArgumentException;

/**
 * A set of resolvers.
 */
class ResolverUtils
{
    /**
     * A map of namespaces.
     *
     * Class name resolver use this map to find out correspond namespace
     * @var string[]
     */
    protected static $namespaces = array(
        'convert_file'                  => 'Tcc\\ConvertFile',
        'convert_file_iterator'         => 'Tcc\\ConvertFile\\Iterator',
        'convertor'                     => 'Tcc\\Convertor',
        'convertor_convert_to_strategy' => 'Tcc\\Convertor\\ConvertToStrategy',
        'script_frontend'               => 'Tcc\\ScriptFrontend',
        'script_frontend_printer'       => 'Tcc\\ScriptFrontend\\Printer',
        'resolver'                      => 'Tcc\\Resolver',
    );

    /**
     * Configuration file resolver
     *
     * @param  SimpleXmlElement $convertInfo
     * @return array
     */
    public static function resolveConvertInfoFromXml(SimpleXMLElement $convertInfo)
    {
        $result = array();

        if (isset($convertInfo->input_charset)) {
            $result['input_charset'] = (string) $convertInfo->input_charset;
        }

        if (isset($convertInfo->output_charset)) {
            $result['output_charset'] = (string) $convertInfo->output_charset;
        }

        if (isset($convertInfo->files)) {
            $result['files'] = array();
            foreach ($convertInfo->files->file as $convertFileInfo) {
                $result['files'][] = static::resolveCommonConvertInfo($convertFileInfo);
            }
        }

        if (isset($convertInfo->dirs)) {
            $result['dirs'] = array();
            foreach ($convertInfo->dirs->dir as $convertDirInfo) {
                $result['dirs'][] = static::resolveConvertDirInfo($convertDirInfo);
            }
        }

        return $result;
    }

    /**
     * Get common information from a convert element.
     *
     * Elememt like files and dirs have some common information to retrive.
     * These code is simply reduce the redundant codes
     *
     * @param  SimpleXmlElement $convertElementInfo
     * @return array
     * @throws InvaidArgumentException If convert element dose not containe name element
     */
    protected static function resolveCommonConvertInfo(SimpleXMLElement $converElementtInfo)
    {
        if (!isset($converElementtInfo->name)) {
            throw new InvalidArgumentException('The needed name field is not provided');
        }

        $result = array();

        $result['name'] = (string) $converElementtInfo->name;

        if (isset($converElementtInfo->input_charset)) {
            $result['input_charset'] = (string) $converElementtInfo->input_charset;
        }

        if (isset($converElementtInfo->output_charset)) {
            $result['output_charset'] = (string) $converElementtInfo->output_charset;
        }

        return $result;
    }

    /**
     * Resolve dirs element.
     *
     * @param  SimpleXmlElement $convertDirInfo
     * @return array
     */
    protected static function resolveConvertDirInfo(SimpleXMLElement $convertDirInfo)
    {
        $result = static::resolveCommonConvertInfo($convertDirInfo);

        if (isset($convertDirInfo->files)) {
            $result['files'] = array();
            foreach ($convertDirInfo->files->file as $convertFile) {
                $result['files'][] = static::resolveCommonConvertInfo($convertFile);
            }
        }

        if (isset($convertDirInfo->subdirs)) {
            $result['subdirs'] = array();
            foreach ($convertDirInfo->subdirs->subdir as $convertSubdirInfo) {
                $result['subdirs'][] = static::resolveConvertDirInfo($convertSubdirInfo, true);
            }
        }

        return $result;
    }

    /**
     * Class name resolver.
     *
     * Reolve class name use namespace identifier and unqualified class name
     *
     * @param  string $nsIdentifier The key of namespaces mapper
     * @param  string $name Unqualified class name
     * @return string Fully qualfied class name
     * @throws InvalidArgumentException If namespace identifier dose not in 
     *         namespaces mapper
     */
    public static function resolveClassName($nsIdentifier, $name)
    {
        if (!isset(static::$namespaces[$nsIdentifier])) {
            throw new InvalidArgumentException('Invalid namespace identifier');
        }

        
        $canonicalName = function($name) {
            return str_replace(' ', '', ucwords(strtr($name, '_', ' ')));
        };

        $namespace = static::$namespaces[$nsIdentifier];
        $className = $namespace . '\\' . $canonicalName($name);

        return (class_exists($className)) ? $className : false;
    }
}
