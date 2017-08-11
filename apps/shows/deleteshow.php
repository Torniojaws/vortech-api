<?php

namespace Apps\Shows;

class DeleteShow extends \Apps\Abstraction\CRUD
{
    public function delete(int $showID)
    {
        if ($this->dbCheck->existsInTable('Shows', 'ShowID', $showID) == false) {
            $response['contents'] = 'Show not found';
            $response['code'] = 400;
            return $response;
        }

        $sql = $this->delete->delete()->from('Shows')->where('ShowID = :id')->result();
        $pdo = array('id' => $showID);
        $this->database->run($sql, $pdo);

        $response['code'] = 204;
        $response['contents'] = array();

        return $response;
    }
}
