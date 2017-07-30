<?php

namespace Apps\Utils;

class Request
{
    public function __construct($server, $get = null, $post = null, $file = null)
    {
        $this->server = $server;
        $this->get = $get;
        $this->post = $post;
        $this->file = $file;
    }

    public function getMethod()
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function getParams()
    {
        return explode('/', $this->get['params']);
    }

    public function hasValidID()
    {
        return is_numeric($this->getParams()[1]);
    }

    public function hasValidJSON(string $json)
    {
        $validator = new \Apps\Utils\Json();
        return $validator->isJson($json);
    }

    public function isMissingRequiredJSON(string $json)
    {
        return (in_array($this->getMethod(), array('POST', 'PUT', 'PATCH'))
            && $this->hasValidJSON($json) == false);
    }

    public function getInvalidIDResponse()
    {
        $response = array();
        $response['contents'] = 'Missing required ID from URL';
        $response['code'] = 400;
        return $response;
    }

    public function getInvalidJSONResponse()
    {
        $response = array();
        $response['contents'] = 'Invalid JSON';
        $response['code'] = 400;
        return $response;
    }
}
