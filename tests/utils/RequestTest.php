<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class RequestTest extends TestCase
{
    public function testConstructor()
    {
        $this->mockserver['REQUEST_METHOD'] = 'POST';
        $request = new \Apps\Utils\Request($this->mockserver);

        $this->assertTrue(isset($request->server));
    }

    public function testGetMethod()
    {
        $this->mockserver['REQUEST_METHOD'] = 'PUT';
        $request = new \Apps\Utils\Request($this->mockserver);
        $expected = 'PUT';

        $this->assertEquals($expected, $request->getMethod());
    }

    public function testGetParamsWithValidData()
    {
        $this->mockserver['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($this->mockserver, $getParams);
        $expected = 123;

        $this->assertEquals($expected, $request->getParams()[1]);
    }

    public function testHasValidIDWhenValidID()
    {
        $this->mockserver['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($this->mockserver, $getParams);

        $this->assertTrue($request->hasValidID());
    }

    public function testHasValidIDWhenInvalidID()
    {
        $this->mockserver['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/invalid');
        $request = new \Apps\Utils\Request($this->mockserver, $getParams);

        $this->assertFalse($request->hasValidID());
    }

    public function testHasValidJSONWithValidJSON()
    {
        $this->mockserver['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($this->mockserver, $getParams);

        $json = '{"hello": "world"}';

        $this->assertTrue($request->hasValidJSON($json));
    }

    public function testHasValidJSONWithInvalidJSON()
    {
        $this->mockserver['REQUEST_METHOD'] = 'GET';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($this->mockserver, $getParams);

        $json = '{"hello';

        $this->assertFalse($request->hasValidJSON($json));
    }

    public function testIsMissingRequiredJSONWithInvalidJSON()
    {
        // PUT and POST require a valid JSON in the API
        $this->mockserver['REQUEST_METHOD'] = 'PUT';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($this->mockserver, $getParams);

        $json = '{"hello';

        $this->assertTrue($request->isMissingRequiredJSON($json));
    }

    public function testIsMissingRequiredJSONWithValidJSON()
    {
        // POST, PUT and PATCH require a valid JSON in the API
        $this->mockserver['REQUEST_METHOD'] = 'PUT';
        $getParams = array('params' => 'news/123');
        $request = new \Apps\Utils\Request($this->mockserver, $getParams);

        $json = '{"hello": "world"}';

        $this->assertFalse($request->isMissingRequiredJSON($json));
    }

    public function testGetInvalidIDResponse()
    {
        $this->mockserver['REQUEST_METHOD'] = 'GET';
        $request = new \Apps\Utils\Request($this->mockserver);
        $result = $request->getInvalidIDResponse()['contents'];
        $expected = 'Missing required ID from URL';

        $this->assertEquals($result, $expected);
    }

    public function testGetInvalidJSONResponse()
    {
        $this->mockserver['REQUEST_METHOD'] = 'PUT';
        $request = new \Apps\Utils\Request($this->mockserver);
        $result = $request->getInvalidJSONResponse()['contents'];
        $expected = 'Invalid JSON';

        $this->assertEquals($result, $expected);
    }
}
