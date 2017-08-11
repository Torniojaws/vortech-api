<?php

namespace Apps\Releases;

class GetRelease extends \Apps\Abstraction\CRUD
{
    public function get(int $releaseID = null)
    {
        $sql = $this->read->select()->from('Releases')->result();
        $pdo = array();

        if (isset($releaseID)) {
            $sql = $this->read->select()->from('Releases')->where('ReleaseID = :id')->result();
            $pdo = array('id' => $releaseID);
        }

        $response['contents'] = $this->database->run($sql, $pdo);
        $response['code'] = 200;

        return $response;
    }
}
