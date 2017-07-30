<?php

namespace Apps\News;

class DeleteNews
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();
    }

    public function delete(int $newsID)
    {
        $check = new \Apps\Utils\DatabaseCheck();
        if ($check->existsInTable('News', 'NewsID', $newsID) == false) {
            $response['contents'] = 'News not found';
            $response['code'] = 400;
            return $response;
        }

        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('News')->where('NewsID = :id')->result();
        $pdo = array('id' => $newsID);
        $this->database->run($sql, $pdo);
    }
}
