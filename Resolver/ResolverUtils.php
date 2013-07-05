<?php
namespace Tcc\Resolver;

use SimpleXMLElement;
use InvalidArgumentException;

class ResolverUtils
{
    protected static $namespaces = array(
        'convert_file'                  => 'Tcc\\ConvertFile',
        'convert_file_iterator'         => 'Tcc\\ConvertFile\\Iterator',
        'convertor'                     => 'Tcc\\Convertor',
        'convertor_convert_to_strategy' => 'Tcc\\Convertor\\ConvertToStrategy',
        'script_frontend'               => 'Tcc\\ScriptFrontend',
        'script_frontend_printer'       => 'Tcc\\ScriptFrontend\\Printer',
        'resolver'                      => 'Tcc\\Resolver',
    );

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

    protected static function resolveConvertDirInfo(SimpleXMLElement $convertDirInfo,
        $isSubdir = false
    ){
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
