<?php
namespace Tcc\Convertor\Strategy;

class RecodeConvertStrategy implements ConvertStrategyInterface
{
	public function convert($contents, $inputCharset, $outputCharset)
	{
		
	}

	public function getName()
	{
		return 'recode';
	}
}
