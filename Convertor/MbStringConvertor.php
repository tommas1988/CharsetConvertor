<?php
/**
 * CharsetConvertor
 * 
 * @author Tommas Yuan
 * @link   http://github.com/tommas1988/CharsetConvertor the source code repository
 */

namespace Tcc\Convertor;

/**
 * MbString convertor
 */
class MbStringConvertor extends AbstractConvertor
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'mbstring';
    }

    /**
     * Convert error handler.
     *
     * This is the wrapper method of {@link AbstractConvertor::convertError()}
     */
    public function convertErrorHandler()
    {
        $this->convertError();
    }

    /**
     * The actual convert method
     */
    protected function doConvert()
    {
        $convertFile       = $this->convertFile;
        $inputCharset      = $convertFile->getInputCharset();
        $outputCharset     = $convertFile->getOutputCharset();
        $convertToStrategy = $this->getConvertToStrategy();

        set_error_handler(array($this, 'convertErrorHandler'), E_WARNING);

        foreach ($convertFile as $line) {
            $convertToStrategy->convertTo(
                mb_convert_encoding($line, $outputCharset, $inputCharset));
        }

        restore_error_handler();
    }
}
