<?php

namespace Apps\Releases;

class DeleteRelease extends \Apps\Abstraction\CRUD
{
    public function delete(int $releaseID)
    {
        if ($this->dbCheck->existsInTable('Releases', 'ReleaseID', $releaseID) == false) {
            $response['contents'] = 'Release not found';
            $response['code'] = 400;
            return $response;
        }

        $sql = $this->delete->delete()->from('Releases')->where('ReleaseID = :id')->result();
        $pdo = array('id' => $releaseID);
        $this->database->run($sql, $pdo);
    }
}
