<?php
namespace Tcc\Test\Convertor;

use Tcc\Convertor\MbStringConvertor;
use PHPUnit_Framework_TestCase;

class MbStringConvertorTest extends PHPUnit_Framework_TestCase
{
    protected $convertor;

    public function setUp()
    {
        $this->convertor = new MbStringConvertor;
    }

    public function testGetName()
    {
        $this->assertEquals('mbstring', $this->convertor->getName());
    }

    public function testDoConvert()
    {
        $convertor         = $this->convertor;
        $convertToStrategy = new MockConvertToStrategy($convertor);
        $convertFile       = new MockConvertFile();

        $convertor->convert($convertFile);
    }

    public function testDoConvertErrorCanRaiseException()
    {
        
    }
}
