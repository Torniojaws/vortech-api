<?php

namespace Apps\News;

class GetNews
{
    public function get(int $newsID = null)
    {
        $database = new \Apps\Database\Database();
        $database->connect();

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select()->from('News')->result();
        $pdoParameters = array();

        if (isset($newsID)) {
            $sql = $sqlBuilder->select()->from('News')->where('NewsID = :id')->result();
            $pdoParameters = array('id' => $newsID);
        }

        $response['contents'] = $database->run($sql, $pdoParameters);
        $response['code'] = 200;
        return $response;
    }
}
