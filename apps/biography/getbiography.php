<?php

namespace Apps\Biography;

class GetBiography extends \Apps\Abstraction\CRUD
{
    public function get()
    {
        $sql = $this->read->select()->from('Biography')->order('Created DESC')->limit(1)->result();
        $pdo = array();
        $result = $this->database->run($sql, $pdo);

        $response['code'] = 200;
        $response['contents'] = $result[0];

        return $response;
    }
}
