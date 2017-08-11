<?php

namespace Apps\People;

class GetPeople extends \Apps\Abstraction\CRUD
{
    public function get(int $personID = null)
    {
        $sql = $this->read->select()->from('People')->result();
        $pdo = array();

        if (is_numeric($personID)) {
            $sql = $this->read->select()->from('People')->where('PersonID = :id')->limit(1)->result();
            $pdo = array('id' => $personID);
        }

        $result = $this->database->run($sql, $pdo);
        $response['code'] = 200;
        $response['contents'] = $result;

        return $response;
    }
}
