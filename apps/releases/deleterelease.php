<?php

namespace Apps\Releases;

class DeleteRelease
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();
    }

    public function delete(int $releaseID)
    {
        $check = new \Apps\Utils\DatabaseCheck();
        if ($check->existsInTable('Releases', 'ReleaseID', $releaseID) == false) {
            $response['contents'] = 'Release not found';
            $response['code'] = 400;
            return $response;
        }

        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Releases')->where('ReleaseID = :id')->result();
        $pdo = array('id' => $releaseID);
        $this->database->run($sql, $pdo);
    }
}
