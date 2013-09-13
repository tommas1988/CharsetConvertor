<?php
/**
 * CharsetConvertor
 * 
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

namespace Tcc\Convertor\ConvertToStrategy;

use Tcc\Convertor\AbstractConvertor;
use Tcc\ConvertFile\ConvertFile;
use RuntimeException;

/**
 * Long name ConvertToStrategy.
 *
 * Generate a file name with each directory name separated by the underscore
 */
class LongNameConvertToStrategy extends AbstractConvertToStrategy
{
    /**
     * Generate a target file name
     *
     * @return string
     * @throws RuntimeException If convert file is not instance of 
     *         Tcc\ConvertFile\ConvertFile
     */
    public function getTargetFileName()
    {
        $convertor   = $this->convertor;
        $convertFile = $convertor->getConvertFile();

        if (!$convertFile instanceof ConvertFile) {
            throw new RuntimeException('Invalid convertFile');
        }

        $transArr = array('\\' => '_', '/' => '_');
        $pathname = strtr($convertFile->getPathname(), $transArr);

        $filename = preg_replace('/^(\\_|[a-zA-Z]\\:\\_)/', '', $pathname);

        return $convertor->getTargetLocation() . '/' . $filename;
    }
}
