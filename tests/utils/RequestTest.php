<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class RequestTest extends TestCase
{
    public function setUp()
    {
        $this->mockserver['REQUEST_METHOD'] = 'PUT';
        $params = array('params' => 'news/123');
        $this->request = new \Apps\Utils\Request($this->mockserver, $params);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->request instanceof \Apps\Utils\Request);
    }

    public function testGetMethod()
    {
        $this->assertEquals('PUT', $this->request->getMethod());
    }

    public function testGetParams()
    {
        $this->assertEquals(123, $this->request->getParams()[1]);
    }

    public function testIsValid()
    {
        $json = '{"test": "case"}';
        $this->assertTrue($this->request->isValid($json));
    }

    public function testValidJSONWhenInvalid()
    {
        $json = '{hello';

        $this->assertFalse($this->request->validJSON($json));
    }

    public function testValidJSONWhenMethodDoesNotRequireJSON()
    {
        $mockserver['REQUEST_METHOD'] = 'GET';
        $params = array('params' => 'news/123');
        $req = new \Apps\Utils\Request($mockserver, $params);
        // We don't need a valid JSON when method (GET, in this case) does not require a JSON
        // since in that case the "JSON" is not used for anything
        $invalid = '{invalid';

        $this->assertTrue($req->validJSON($invalid));
    }
}
