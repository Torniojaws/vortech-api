<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class RequestTest extends TestCase
{
    public function testConstructor()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new \Apps\Utils\Request($_SERVER);

        $this->assertTrue(isset($request->server));
    }

    public function testGetMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request = new \Apps\Utils\Request($_SERVER);
        $expected = 'PUT';

        $this->assertEquals($expected, $request->getMethod());
    }

    public function testGetParamsWithValidData()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($_SERVER, $getParams);
        $expected = 123;

        $this->assertEquals($expected, $request->getParams()[1]);
    }

    public function testHasValidIDWhenValidID()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($_SERVER, $getParams);

        $this->assertTrue($request->hasValidID());
    }

    public function testHasValidIDWhenInvalidID()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/invalid');
        $request = new \Apps\Utils\Request($_SERVER, $getParams);

        $this->assertFalse($request->hasValidID());
    }

    public function testHasValidJSONWithValidJSON()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($_SERVER, $getParams);

        $json = '{"hello": "world"}';

        $this->assertTrue($request->hasValidJSON($json));
    }

    public function testHasValidJSONWithInvalidJSON()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($_SERVER, $getParams);

        $json = '{"hello';

        $this->assertFalse($request->hasValidJSON($json));
    }

    public function testIsMissingRequiredJSONWithInvalidJSON()
    {
        // PUT and POST require a valid JSON in the API
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($_SERVER, $getParams);

        $json = '{"hello';

        $this->assertTrue($request->isMissingRequiredJSON($json));
    }

    public function testIsMissingRequiredJSONWithNoJSON()
    {
        // PUT and POST require a valid JSON in the API
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($_SERVER, $getParams);

        $json = null;

        $this->assertTrue($request->isMissingRequiredJSON($json));
    }

    public function testIsMissingRequiredJSONWithValidJSON()
    {
        // PUT and POST require a valid JSON in the API
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($_SERVER, $getParams);

        $json = '{"hello": "world"}';

        $this->assertFalse($request->isMissingRequiredJSON($json));
    }

    public function testIsMissingRequiredJSONWhenNoJSONIsNeeded()
    {
        // GET and DELETE do not require a JSON
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($_SERVER, $getParams);

        $json = null;

        $this->assertFalse($request->isMissingRequiredJSON($json));
    }

    public function testGetInvalidIDResponse()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new \Apps\Utils\Request($_SERVER);
        $result = $request->getInvalidIDResponse()['contents'];
        $expected = 'Missing required ID from URL';

        $this->assertEquals($result, $expected);
    }

    public function testGetInvalidJSONResponse()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request = new \Apps\Utils\Request($_SERVER);
        $result = $request->getInvalidJSONResponse()['contents'];
        $expected = 'Invalid JSON';

        $this->assertEquals($result, $expected);
    }
}
