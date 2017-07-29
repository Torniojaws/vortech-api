<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class ArraysTest extends TestCase
{
    public function setUp()
    {
        $this->utils = new \Apps\Utils\Arrays();
    }

    public function testBasicArrayFlatteningWithIntegers()
    {
        $arrays = array();

        $name = 'CategoryID';
        $one = array($name => 1);
        $two = array($name=> 2);
        $three = array($name => 4);

        $arrays[] = $one;
        $arrays[] = $two;
        $arrays[] = $three;

        $expected = array(1, 2, 4);
        $flattened = $this->utils->flattenArray($arrays, $name);

        $this->assertEquals($expected, $flattened);
    }

    public function testMixedAlphanumericArrayFlattening()
    {
        $arrays = array();

        $name = 'CategoryID';
        $one = array($name => 1);
        $two = array($name=> 'Stuff');
        $three = array($name => 2);

        $arrays[] = $one;
        $arrays[] = $two;
        $arrays[] = $three;

        $expected = array(1, 'Stuff', 2);
        $flattened = $this->utils->flattenArray($arrays, $name);

        $this->assertEquals($expected, $flattened);
    }

    public function testConversionToFlattenedIntArray()
    {
        $arrays = array();

        $name = 'CategoryID';
        $one = array($name => 1);
        $two = array($name=> 'Stuff');
        $three = array($name => '4');

        $arrays[] = $one;
        $arrays[] = $two;
        $arrays[] = $three;

        $expected = array(1, 4);
        $flat = $this->utils->flattenArray($arrays, $name);
        $intArray = $this->utils->toIntArray($flat);

        $this->assertEquals($expected, $intArray);
    }

    public function testArrayContainsNonIntegersWithIntegerArray()
    {
        $test = [1, 2, 3];
        $hasNonIntegers = $this->utils->arrayContainsNonIntegers($test);

        $this->assertFalse($hasNonIntegers);
    }

    public function testArrayContainsNonIntegersWithNonIntegerArray()
    {
        $test = [1, "ABC", 3];
        $hasNonIntegers = $this->utils->arrayContainsNonIntegers($test);

        $this->assertTrue($hasNonIntegers);
    }
}
