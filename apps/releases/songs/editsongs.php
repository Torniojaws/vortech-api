<?php

namespace Apps\Releases\Songs;

class EditSongs
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->insert = new \Apps\Database\Insert();
    }

    /**
     * We receive a new integer array with references to songs.
     * @param int $releaseID is the release to update
     * @param json $json is the JSON we use to patch the data
     * @return array $response Contains the response we want to send
     */
    public function edit($releaseID, $json)
    {
        $edit = json_decode($json, true);

        if (empty($edit)) {
            $response['contents'] = 'Must have data in the request';
            $response['code'] = 400;
            return $response;
        }

        // Every song must exist in the Songs table. If one of the songs does not exist, nothing
        // will be edited.
        if ($this->allSongsExist($edit) == false) {
            $response['contents'] = 'One of the songs was not found';
            $response['code'] = 400;
            return $response;
        }

        $this->editSongs($edit, $releaseID);

        $response['contents'] = 'Location: http://www.vortechmusic.com/api/1.0/releases/'.$releaseID.'/songs';
        $response['code'] = 200;
        return $response;
    }

    /**
     * Replace the existing songlist of the release with the new list
     * @param int[] $songs Contains an array of song IDs
     * @param int $releaseID is the release we will edit
     */
    public function editSongs($songs, $releaseID)
    {
        $this->removePreviousSongs($releaseID);

        foreach ($songs as $songID) {
            $sql = $this->insert->insert()->into('ReleaseSongs(ReleaseID, SongID)')
                ->values(':rid, :song')->result();
            $pdo = array('rid' => $releaseID, 'song' => $songID);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * We replace the existing references, so let's delete the old references. This only deletes the
     * release references - the songs themselves will still remain in the Songs table.
     * @param int $releaseID is the ID of the release
     */
    public function removePreviousSongs($releaseID)
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('ReleaseSongs')->where('ReleaseID = :id')->result();
        $pdo = array('id' => $releaseID);
        $this->database->run($sql, $pdo);
    }

    /**
     * If any song is missing from Songs table, we cancel the entire edit process.
     * @param int[] $songs has the song IDs we check. The method needs it as a string, so it is cast
     * @return boolean
     */
    public function allSongsExist($songs)
    {
        $missingCount = 0;
        $databaseCheck = new \Apps\Utils\DatabaseCheck();
        foreach ($songs as $songID) {
            if ($databaseCheck->existsInTable('Songs', 'SongID', (string)$songID) == false) {
                $missingCount++;
            }
        }

        return $missingCount == 0;
    }
}
