<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class JsonTest extends TestCase
{
    public function __construct()
    {
        $this->jsonValidator = new \Apps\Utils\Json();
    }

    public function testValidJsonObjectSimple()
    {
        $json = '{"test": "data", "choice": 1}';
        $isValid = $this->jsonValidator->isJson($json);

        $this->assertTrue($isValid);
    }

    public function testInvalidJsonObjectWithMissingQuotes()
    {
        // "Quotes" around keys and values are required
        $json = '{test: data, "choice": 1}';
        $isValid = $this->jsonValidator->isJson($json);

        $this->assertFalse($isValid);
    }

    public function testInvalidJsonObjectWithApostrophesInsteadOfQuotes()
    {
        // PHP allows apostrophes, but they are not valid JSON according to spec
        $json = "{'test': 'data', 'choice': 1}";
        $isValid = $this->jsonValidator->isJson($json);

        $this->assertFalse($isValid);
    }

    public function testValidJsonObjectComplex()
    {
        // Adding a new album might get this complex
        $json = '{"data": [{"stuff": 123, "things": "smooth"}],
            "more": [{"things": [{"change": "MH"}, {"down": "none"}]}]}';
        $isValid = $this->jsonValidator->isJson($json);

        $this->assertTrue($isValid);
    }

    public function testValidJsonArraySimpleWithUnicode()
    {
        $json = '[{"hello": "你好"}, {"mitä îhmëttáe": "привет"}]';
        $isValid = $this->jsonValidator->isJson($json);

        $this->assertTrue($isValid);
    }

    public function testInvalidJsonArraySimpleWithMissingClosingBracket()
    {
        $json = '[{"hello": "你好"}, {"mitä îhmëttáe": "привет"}';
        $isValid = $this->jsonValidator->isJson($json);

        $this->assertFalse($isValid);
    }
}
