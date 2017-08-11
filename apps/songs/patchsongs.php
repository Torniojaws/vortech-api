<?php

namespace Apps\Songs;

class PatchSongs extends \Apps\Abstraction\CRUD
{
    public function patch(int $songID, string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        try {
            $data = json_decode($json, true);
            foreach ($data as $column => $value) {
                $sql = $this->update->update('Songs')->set($column.' = :value')
                    ->where('SongID = :id')->result();
                $pdo = array('value' => $value, 'id' => $songID);
                $this->database->run($sql, $pdo);
            }
            $response['code'] = 204;
            $response['contents'] = array();
        } catch (\PDOException $ex) {
            $response['code'] = 400;
            $response['contents'] = 'Could not patch data';
        }

        return $response;
    }
}
