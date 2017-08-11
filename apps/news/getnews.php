<?php

namespace Apps\News;

class GetNews extends \Apps\Abstraction\CRUD
{
    public function get(int $newsID = null)
    {
        $sql = $this->read->select()->from('News')->result();
        $pdo = array();

        if (isset($newsID)) {
            $sql = $this->read->select()->from('News')->where('NewsID = :id')->result();
            $pdo = array('id' => $newsID);
        }

        $response['contents'] = $this->database->run($sql, $pdo);
        $response['code'] = 200;
        return $response;
    }
}
