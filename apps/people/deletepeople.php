<?php

namespace Apps\People;

class DeletePeople
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->delete = new \Apps\Database\Delete();
    }

    public function delete(int $personID)
    {
        // If ID does not exist, we return 400
        $check = new \Apps\Utils\DatabaseCheck();
        if ($check->existsInTable('People', 'PersonID', $personID) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid PersonID';
            return $response;
        }

        $sql = $this->delete->delete()->from('People')->where('PersonID = :id')->result();
        $pdo = array('id' => $personID);
        $this->database->run($sql, $pdo);

        $response['code'] = 204;
        $response['contents'] = array();

        return $response;
    }
}
