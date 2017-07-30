<?php

namespace Apps\Releases\Songs;

class GetSongs
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->sql = new \Apps\Database\Select();
    }

    /**
     * Get the songs for the given ReleaseID
     * @param int $releaseID Is the release we look for
     */
    public function get(int $releaseID)
    {
        $sql = $this->sql->select()->from('ReleaseSongs')
            ->joins('JOIN Songs ON Songs.SongID = ReleaseSongs.SongID')
            ->where('ReleaseSongs.ReleaseID = :id')->result();
        $pdo = array('id' => $releaseID);

        $response['contents'] = $this->database->run($sql, $pdo);
        $response['code'] = 200;
        return $response;
    }
}
