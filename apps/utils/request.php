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

    public function getFilters()
    {
        // We want everything except the key "params", and the other keys can be anything
        // Also, we don't want to modify the original GET
        $filters = $this->get;
        unset($filters['params']);
        return $filters;
    }

    /**
     * We check that the request contains a valid ID or no ID.
     * If the request is POST, PUT or PATCH, we also require a valid JSON
     * @param string $json Is an optional value that should contain a JSON
     * @return boolean
     */
    public function isValid(string $json = null)
    {
        return $this->validID() && $this->validJSON($json);
    }

    /**
     * ID must be either null or a valid number
     * @return boolean
     */
    public function validID()
    {
        $currentID = isset($this->getParams()[1]) ? $this->getParams()[1] : null;

        return $currentID == null || is_numeric($currentID);
    }

    /**
     * When method is POST, PUT or PATCH, a valid JSON is required
     * @param string $json is the optional JSON
     * @return boolean
     */
    public function validJSON(string $json = null)
    {
        $requiresJSON = array('POST', 'PUT', 'PATCH');

        // When method does not require a JSON, we return early
        if (in_array($this->getMethod(), $requiresJSON) == false) {
            return true;
        }

        if (empty($json)) {
            return false;
        }

        $validator = new \Apps\Utils\Json();

        return $validator->isJson($json);
    }
}
