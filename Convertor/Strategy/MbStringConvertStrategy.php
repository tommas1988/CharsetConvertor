<?php
namespace Tcc\Convertor\Strategy;

class MbStringConvertStrategy implements ConvertStrategyInterface
{
	public function convert($contents, $inputCharset, $outputCharset)
	{
		
	}

	public function getName()
	{
		return 'mbstring';
	}
}
