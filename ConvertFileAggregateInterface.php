<?php
namespace Tcc;

interface ConvertFileAggregateInterface
{
	public function addConvertFiles(ConvertFileContainerInterface $container);
	public function getConvertFiles();
}
