<?php
namespace Tcc\Test\ConvertFile\Mock;

use Tcc\ConvertFile\ConvertFileAggregateInterface;
use Tcc\ConvertFile\ConvertFileContainerInterface;

class MockConvertFileAggregate implements ConvertFileAggregateInterface
{
    protected $container;

    public function addConvertFiles(ConvertFileContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getConvertFiles()
    {
        $convertFileFoo = new MockConvertFile();
        $convertFileBar = new MockConvertFile();
        $convertFileFoo->setExtension('test');
        $convertFileBar->setExtension('test');

        $this->container->addFile($convertFileFoo);
        $this->container->addFile($convertFileBar);
    }
}
