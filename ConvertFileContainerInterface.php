<?php
namespace Tcc;

interface ConvertFileContainerInterface
{
	public function addFile($convertFile);
	public function addFiles(ConvertFileAggregateInterface $aggregate);
	public function getConvertFiles();
	public function setConvertExtensions(array $extensions);
	public function getConvertExtensions();
}
