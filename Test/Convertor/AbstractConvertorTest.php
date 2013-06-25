<?php
namespace Tcc\Test\Convertor;

use Tcc\Test\Convertor\TestAssert\FooConvertor;
use Tcc\Test\Convertor\Mock\MockConvertToStrategy;
use PHPUnit_Framework_TestCase;

class AbstractConvertorTest extends PHPUnit_Framework_TestCase
{
    protected $convertor;

    public function setUp()
    {
        $convertor = new FooConvertor;
        $convertor->setConvertToStrategy(new MockConvertToStrategy);
    }
}
