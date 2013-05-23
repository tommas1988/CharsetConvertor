<?php
class Convertor
{
	protected $availableConvertStrategy;
	protected $convertStrategy;
	
	public function __construct()
	{

	}

	public function checkEnvrionment($convertStrategy)
	{
		if (is_string($convertStrategy)) {
			$strategyName = $convertStrategy;
		} elseif ($convertStrategy instanceof ConvertStrategyInterface) {
			$strategyName = $convertStrategy->getName();
		} else {
			throw new Exception();
		}

		$strategyNames = $this->getAvailableConvertStrategy()

		return in_array(strtolower($strategyName), $strategyNames);
	}

	public function setStrategy($strategy)
	{
		if (!$this->checkEnvrionment($strategy)) {
			throw new Exception();
		}

		if (is_string($strategy)) {
			$strategyClass = static::getStrategyClass($strategy);
			$strategy = new $strategyClass;
		}

		$this->convertStrategy = $strategy;
	}

	public function getAvailableConvertStrategy()
	{
		if ($this->$availableConvertStrategy !== null) {
			return $this->$availableConvertStrategy;
		}
		
		$extensions = get_loaded_extensions();
		
		$this->$availableConvertStrategy = array();
		if (in_array('iconv', $extensions)) {
			$this->$availableConvertStrategy[] = 'iconv';
		}
		if (in_array('recode', $extensions)) {
			$this->$availableConvertStrategy[] = 'recode';
		}
		if (in_array('mbstring', $extensions)) {
			$this->$availableConvertStrategy[] = 'mbstring';
		}
		
		return $this->$availableConvertStrategy;
	}

	public static function getStrategyClass($strategyName)
	{
		$strategyMap = array(
				'iconv'    => 'IConvConvertStrategy',
				'recode'   => 'RecodeCOnvertStrategy',
				'mbstring' => 'MbStringConvertStrategy',
			);

		$strategyName = strtolower($strategyName);

		if (!isset($strategyMap[$strategyName])) {
			return false;
		}

		return $strategyMap[$strategyName];
	}
}
