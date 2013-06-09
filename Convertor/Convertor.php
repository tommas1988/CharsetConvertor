<?php
namespace Tcc\Convertor;

use Tcc\Convertor\Strategy\ConvertStrategyInterface;
use Tcc\ConvertFileInterface;

class Convertor
{
    protected $convertStrategy;
    
    public function __construct(ConvertStrategyInterface $convertStrategy)
    {
        $strategyName = $convertStrategy->getName();

        if (!static::checkEnvionment($strategyName)) {
            throw new \Exception();
        }

        $this->convertStrategy = $convertStrategy;
    }

    public function convert(ConvertFileInterface $convertFile)
    {
        $inputCharset      = $convertFile->getInputCharset();
        $outputCharset     = $convertFile->getOutputCharset();
        $convertedContents = '';

        foreach ($convertFile as $line) {
            $convertedContents .= $this->convertStrategy->convert($line, $inputCharset, $outputCharset);
        }

        $path     = $convertFile->getPath();
        $filename = $convertFile->getFilename(true) . '_convert.' . $convertFile->getExtension();
        $pathname = $path . '/' . $filename;

        if (!file_put_contents($pathname, $convertedContents)) {
            throw new \Exception();
        }
    }

    public static function checkEnvrionment($convertStrategy = null)
    {
        if (is_string($convertStrategy)) {
            $strategyName = $convertStrategy;
        } elseif ($convertStrategy instanceof ConvertStrategyInterface) {
            $strategyName = $convertStrategy->getName();
        } else {
            throw new \Exception();
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

        if ($convertStrategy === null) {
            return count($convertExtensions) > 0;
        }

        return in_array(strtolower($strategyName), $convertExtensions);
    }

    public static function getConvertStrategyClass($strateyName)
    {
        if (!is_string($strategyName)) {
            throw new \Exception();
        }

        $strategyClassMap = array(
            'iconv'    => 'Tcc\\Convertor\Strategy\\IConvConvertStrategy',
            'recode'   => 'Tcc\\Convertor\Strategy\\RecodeConvertStrategy',
            'mbstring' => 'Tcc\\Convertor\Strategy\\MbStringConvertStrategy',
        );

        $strategyName = strtolower($strategyName);
        if (in_array($strategyName, $strategyClassMap)) {
            return $strategyClassMap[$strategyName];
        }

        return false;
    } 
}
