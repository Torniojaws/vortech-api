<?php

namespace Apps\Songs;

class PatchSongs
{
    public function patch(int $songID, string $json)
    {
        $validator = new \Apps\Utils\Json();
        if ($validator->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $database = new \Apps\Database\Database();
        $database->connect();

        $patch = new \Apps\Database\Update();

        try {
            $data = json_decode($json, true);
            foreach ($data as $column => $value) {
                $sql = $patch->update('Songs')->set($column.' = :value')
                    ->where('SongID = :id')->result();
                $pdo = array('value' => $value, 'id' => $songID);
                $database->run($sql, $pdo);
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
