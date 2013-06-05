<?php
class MockConvertFileAggregate implements Tcc\ConvertFileAggregateInterface
{
	public function addConvertFiles(Tcc\ConvertFileContainer $container)
	{

	}

	public function getConvertFiles()
	{
		return array(
			new MockConvertFile(),
			new MockConvertFile(),
		);
	}
}
