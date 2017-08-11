<?php

namespace Apps\News;

class DeleteNews extends \Apps\Abstraction\CRUD
{
    public function delete(int $newsID)
    {
        if ($this->dbCheck->existsInTable('News', 'NewsID', $newsID) == false) {
            $response['contents'] = 'News not found';
            $response['code'] = 400;
            return $response;
        }

        $sql = $this->delete->delete()->from('News')->where('NewsID = :id')->result();
        $pdo = array('id' => $newsID);
        $this->database->run($sql, $pdo);
    }
}
