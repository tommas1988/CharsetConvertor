<?php
namespace Tcc\Resolver;

class ResolverUtils
{
    protected static $namespaces = array(
        'convert_file'                  => 'Tcc\\ConvertFile',
        'convert_file_iterator'         => 'Tcc\\ConvertFile\\Iterator',
        'convertor'                     => 'Tcc\\Convertor',
        'convertor_convert_to_strategy' => 'Tcc\\Convertor\\ConvertToStrategy',
        'script_frontend'               => 'Tcc\\ScriptFrontend',
    );

    public static function resolveConvertInfoFromXML(SimpleXMLElement $convertInfo)
    {
        //simply reduce the redundant code
        $getCommonInfo = function (SimpleXMLElement $info) {
            if (!isset($info->name)) {
                throw new InvalidArgumentException('The needed name field is not provided');
            }

            $result = array();

            $result['name'] = $info->name;

            if (isset($info->input_charset)) {
                $result['input_charset'] = $info->input_charset;
            }

            if (isset($info->output_charset)) {
                $result['output_charset'] = $info->output_charset;
            }

            return $result;
        };

        $result = array();

        if (isset($convertInfo->input_charset)) {
            $result['input_charset'] = $convertInfo->input_charset;
        }

        if (isset($convertInfo->output_charset)) {
            $result['output_charset'] = $convertInfo->output_charset;
        }

        if (isset($convertInfo->files)) {
            $result['files'] = array();
            foreach ($convertInfo->files->file as $convertFileInfo) {
                $result['files'][] = $getCommonInfo($convertFileInfo);
            }
        }

        if (isset($convertInfo->dirs)) {
            $result['dirs'] = array();
            $count = 0;
            foreach ($convertInfo->dirs->dir as $convertDirInfo) {
                $result['dirs'][$count] = $getCommonInfo($convertDirInfo);

                if (isset($convertDirInfo->subdirs)) {
                    $result['dirs'][$count]['subdirs'] = array();
                    foreach ($convertDirInfo->subdirs->subdir as $subDirInfo) {
                        $result['dirs'][$count]['subdirs'][] = $this->resolveConvertInfoFromXML($subdirInfo);
                    }
                }
                
                $count++;
            }
        }

        return $result;
    }

    public static function resolveClassName($nsIdentifier, $name, $suffix)
    {
        if (!isset(static::namespaces[$nsIdentifier])) {
            throw new InvalidArgumentException('Invalid namespace identifier');
        }

        $canonicalName = function($name) {
            return strtr(ucwords(strtr($name, '_', ' ')), ' ', '');
        };

        $namespace = $this->namespaces[$nsIdentifier];
        $className = $namespace . '//' . canonicalName($name . '_' . $suffix);

        return (class_exists($className)) ? $className : false;
    }
}
