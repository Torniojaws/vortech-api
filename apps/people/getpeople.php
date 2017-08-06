<?php

namespace Apps\People;

class GetPeople
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->select = new \Apps\Database\Select();
    }

    public function get(int $personID = null)
    {
        $sql = $this->select->select()->from('People')->result();
        $pdo = array();

        if (is_numeric($personID)) {
            $sql = $this->select->select()->from('People')->where('PersonID = :id')->limit(1)->result();
            $pdo = array('id' => $personID);
        }

        $result = $this->database->run($sql, $pdo);
        $response['code'] = 200;
        $response['contents'] = $result;

        return $response;
    }
}
