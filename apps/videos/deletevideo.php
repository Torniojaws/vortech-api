<?php

namespace Apps\Videos;

class DeleteVideo extends \Apps\Abstraction\CRUD
{
    public function delete(int $videoID)
    {
        if ($this->dbCheck->existsInTable('Videos', 'VideoID', $videoID) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Show not found';
            return $response;
        }

        $sql = $this->delete->delete()->from('Videos')->where('VideoID = :id')->result();
        $pdo = array('id' => $videoID);
        $this->database->run($sql, $pdo);

        $response['code'] = 204;
        $response['contents'] = array();
        return $response;
    }
}
