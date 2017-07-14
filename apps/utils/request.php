<?php

namespace VortechAPI\Apps\Utils;

class RequestHandler
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
}
