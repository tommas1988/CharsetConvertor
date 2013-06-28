<?php
namespace Tcc\Test\Convertor;

use Tcc\Test\Convertor\TestAssert\ConvertorFactory;
use Tcc\Test\Convertor\TestAssert\FooConvertor;
use PHPUnit_Framework_TestCase;

class CovnertorFactory extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        ConvertorFactory::setConvertors(array(
            'foo'      => 'Tcc\\Test\\Convertor\\TestAssert\\FooConvertor',
            'mbstring' => 'Tcc\\Convertor\\MbStringConvertor',
        ));
    }

    public function testCheckEnvrionmentWithInvalidArgumentCanRaiseExecption()
    {
        $this->setExpectedException('InvalidArgumentException');

        ConvertorFactory::checkEnvrionment(false);
    }

    public function testCheckEnvrionmentWithAvailableConvertorName()
    {
        $this->assertTrue(ConvertorFactory::checkEnvrionment('Foo'));
    }

    public function testCheckEnvrionmentWithAvailableConvertor()
    {
        $convertor = new FooConvertor;

        $this->assertTrue(ConvertorFactory::checkEnvrionment($convertor));
    }

    public function testCheckEnvrionmentWithoutAvailableConvertor()
    {
        $this->assertFalse(ConvertorFactory::checkEnvrionment('invalid-convertor'));

        ConvertorFactory::setConvertors(false);
        $this->assertFalse(ConvertorFactory::checkEnvrionment());
    }

    public function testFactoryCanRaiseExceptionIfProvidedConvertorIsNotAvailable()
    {
        $this->setExpectedException('RuntimeException');

        ConvertorFactory::factory('invalid-convertor');
    }

    public function testFactoryReturnASpecificConvertor()
    {
        $this->assertInstanceOf('Tcc\\Convertor\\MbStringConvertor',
            ConvertorFactory::factory('mbstring'));
    }

    public function testFactoryCanReturnTheFirstAvailableConvertorIfNotPassArgument()
    {
        $this->assertInstanceOf('Tcc\\Test\\Convertor\\TestAssert\\FooConvertor',
            ConvertorFactory::factory());
    }
}
