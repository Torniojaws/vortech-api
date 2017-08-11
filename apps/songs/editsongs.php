<?php

namespace Apps\Songs;

class EditSongs extends \Apps\Abstraction\CRUD
{
    /**
     * Update the SongID in the database and return the status
     * @param int $songID is the song we edit
     * @param string $json contains the JSON of the new data
     * @return array $response contains the result of the run
     */
    public function edit(int $songID, string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        if ($this->dbCheck->existsInTable('Songs', 'SongID', $songID) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid value';
            return $response;
        }

        $song = json_decode($json, true);

        $sql = $this->update->update('Songs')->set('Title = :title, Duration = :duration')
            ->where('SongID = :id')->result();
        $pdo = array('title' => $song['title'], 'duration' => $song['duration'], 'id' => $songID);
        $this->database->run($sql, $pdo);

        $response['code'] = 200;
        $response['contents'] = array();

        return $response;
    }
}
