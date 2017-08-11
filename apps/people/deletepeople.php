<?php

namespace Apps\People;

class DeletePeople extends \Apps\Abstraction\CRUD
{
    public function delete(int $personID)
    {
        // If ID does not exist, we return 400
        if ($this->dbCheck->existsInTable('People', 'PersonID', $personID) == false) {
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
