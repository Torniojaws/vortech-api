<?php

namespace Apps\Photos;

class DeletePhotos extends \Apps\Abstraction\CRUD
{
    public function delete(int $photoID)
    {
        $exists = $this->dbCheck->existsInTable('Photos', 'PhotoID', $photoID);
        if ($exists == false) {
            $response['code'] = 404;
            $response['contents'] = array();
            return $response;
        }

        $sql = $this->delete->delete()->from('Photos')->where('PhotoID = :id')->result();
        $pdo = array('id' => $photoID);
        $this->database->run($sql, $pdo);

        $response['code'] = 204;
        $response['contents'] = array();

        return $response;
    }
}
