<?php

namespace Apps\Releases;

class GetRelease
{
    public function get($releaseID = null)
    {
        $database = new \Apps\Database\Database();
        $database->connect();

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select()->from('Releases')->result();
        $pdoParameters = array();

        if (is_numeric($releaseID)) {
            $sql = $sqlBuilder->select()->from('Releases')->where('ReleaseID = :id')->result();
            $pdoParameters = array('id' => $releaseID);
        }

        $response['contents'] = $database->run($sql, $pdoParameters);
        $response['code'] = 200;
        return $response;
    }
}
