<?php
namespace Tcc\Test\Resolver;

use Tcc\Resolver\ResolverUtils as Resolver;
use SimpleXmlElement;
use DOMDocument;
use DOMXPath;
use PHPUnit_Framework_TestCase;

class ResolverUtilsTest extends PHPUnit_Framework_TestCase
{
    public static function validConvertInfo()
    {
        return array(
            array(new SimpleXMLElement('./Resolver/_files/convert_info.xml', 0, true)),
        );
    }

    public static function invalidConvertInfo()
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->load(realpath('./Resolver/_files/convert_info.xml'));

        $xpath = new DOMXPath($dom);
        $name  = $xpath->query('//convert_info/files/file/name')->item(0);

        $dom->removeChild($name);

        return array(
            array(simplexml_import_dom($dom)),
        );
    }

    /**
     * @dataProvider validConvertInfo
     */
    public function testResolveConvertInfoFromXml($data)
    {
        $expected = array(
            'input_charset'  => 'g-utf8',
            'output_charset' => 'g-ansi',
            'files' => array(
                array(
                    'name'           => 'foo',
                    'input_charset'  => 'f-ansi',
                    'output_charset' => 'f-utf8',
                ),
                array('name' => 'bar'),
            ),
            'dirs' => array(
                array(
                    'name'           => 'foo',
                    'input_charset'  => 'd-utf8',
                    'output_charset' => 'd-ansi',
                    'files' => array(
                        array('name' => 'd-f-foo'),
                    ),
                    'subdirs' => array(
                        array(
                            'name'           => 'sub-dir',
                            'input_charset'  => 's-d-utf8',
                            'output_charset' => 's-d-ansi',
                            'files' => array(
                                array('name' => 's-d-f-foo'),
                            ),
                            'subdirs' => array(
                                array('name' => 'sub-sub-dir'),
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertSame($expected, Resolver::resolveConvertInfoFromXml($data));
    }

    /**
     * @dataProvider invalidConvertInfo   
     */
    public function testResolveConvertInfoFromCanRaiseExceptionIfFileOrDirNodeDoseContainNameNode($data)
    {
        $this->setExpectedException('InvalidArgumentException');

        Resolver::resolveConvertInfoFromXml($data);
    }

    public function testResolveClassName()
    {
        $this->assertSame('Tcc\\ConvertFile\\ConvertFile',
            Resolver::resolveClassName('convert_file', 'convert_file'));
        $this->assertSame('Tcc\\ConvertFile\\Iterator\\ConvertDirectoryIterator',
            Resolver::resolveClassName('convert_file_iterator', 'convert_directory_iterator'));
        $this->assertSame('Tcc\\Convertor\\ConvertorFactory',
            Resolver::resolveClassName('convertor', 'convertor_factory'));
        $this->assertSame('Tcc\\Convertor\\ConvertToStrategy\\LongNameConvertToStrategy',
            Resolver::resolveClassName('convertor_convert_to_strategy', 'long_name_convert_to_strategy'));
        $this->assertSame('Tcc\\ScriptFrontend\\Runner',
            Resolver::resolveClassName('script_frontend', 'runner'));
        $this->assertSame('Tcc\\ScriptFrontend\\Printer\\ConsolePrinter',
            Resolver::resolveClassName('script_frontend_printer', 'console_printer'));
        $this->assertSame('Tcc\\Resolver\\ResolverUtils',
            Resolver::resolveClassName('resolver', 'resolver_utils'));
    }

    public function testResolveClassNameCanRaiseExceptionWithUnrecognizeNamespaceIdentifier()
    {
        $this->setExpectedException('InvalidArgumentException');

        Resolver::resolveClassName('not_exists_ns', 'convert_file');
    }

    public function testResolveClassNameReturnFalseWhenClassNotExists()
    {
        $this->assertFalse(Resolver::resolveClassName('convertor', 'not_exists_class'));
    }
}
