<?php

namespace Apps\Shows;

class DeleteShow
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();
    }

    public function delete(int $showID)
    {
        $check = new \Apps\Utils\DatabaseCheck();
        if ($check->existsInTable('Shows', 'ShowID', $showID) == false) {
            $response['contents'] = 'Show not found';
            $response['code'] = 400;
            return $response;
        }

        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Shows')->where('ShowID = :id')->result();
        $pdo = array('id' => $showID);
        $this->database->run($sql, $pdo);
    }
}
