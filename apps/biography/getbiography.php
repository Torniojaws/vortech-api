<?php

namespace Apps\Biography;

class GetBiography
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->select = new \Apps\Database\Select();
    }

    public function get()
    {
        $sql = $this->select->select()->from('Biography')->order('Created DESC')->limit(1)->result();
        $pdo = array();
        $result = $this->database->run($sql, $pdo);

        $response['code'] = 200;
        $response['contents'] = $result[0];

        return $response;
    }
}
