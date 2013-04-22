<?php
class ConvertorFactory
{
	protected static $defaultConvertor = 'iconv';
	protected static $availableConvertors;
	
	public static function factory($convertorName = NULL)
	{
		
	}
	
	public static function checkEnverioment()
	{	
		self::getAvailableConvertors();
		
		return count(self::$availableConvertors) > 0;
	}
	
	public static function getAvailableConvertors()
	{
		if (self::$availableConvertors !== null) {
			return self::$availableConvertors;
		}
		
		$extensions = get_loaded_extensions();
		
		self::$availableConvertors = array();
		if (in_array('iconv', $extensions)) {
			self::$availableConvertors[] = 'iconv';
		}
		if (in_array('recode', $extensions)) {
			self::$availableConvertors[] = 'recode';
		}
		if (in_array('mbstring', $extensions)) {
			self::$availableConvertors[] = 'mbstring';
		}
		
		return self::$availableConvertors;
	}
}