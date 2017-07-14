<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

class JsonToolsTest extends TestCase
{
    public function __construct()
    {
        require_once('apps/utils/json.php');
        $this->jsonValidator = new \VortechAPI\Apps\Utils\JsonTools();
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
        $json = '{"data": [{"stuff": 123, "things": "smooth"}], "more": [{"things": [{"change": "MH"}, {"down": "none"}]}]}';
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
