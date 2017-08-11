<?php

namespace Apps\Songs;

class AddSongs extends \Apps\Abstraction\CRUD
{
    /**
     * The song we receive will be inserted to Songs table. There are two possibilities in the data
     * we get. It can either be a single JSON Array, or it can be an array of JSON arrays when several
     * songs are being added.
     * @param array $data Contains the songs to add
     */
    public function add(array $data)
    {
        // To make things straightforward, we simply make an array of arrays when a simple array
        // is passed, so we can use foreach for both cases without some extra logic duplication
        if ($this->isSimpleArray($data) == true) {
            $data = array($data);
        }

        // Iterate over each song object and insert it to the DB
        $invalidCount = 0;
        foreach ($data as $song) {
            $valid = isset($song['title']) && isset($song['duration']) ? true : false;
            if ($valid == false) {
                $invalidCount++;
                continue;
            }
            $this->insertSong($song);
        }

        $response['code'] = 201;
        $response['contents'] = 'Location: http://www.vortechmusic.com/api/1.0/songs';

        if ($invalidCount == count($data)) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid data';
        }

        return $response;
    }

    /**
     * When a single song is passed, it will be a simple array with keys "title" and "duration"
     * @param array $data contains the array we check
     * @return boolean Is it a flat array or no
     */
    public function isSimpleArray(array $data)
    {
        return array_key_exists('title', $data);
    }

    /**
     * Insert the passed song object to the database and return the ID it was inserted as
     * @param array $song is the data of the song
     * @return int Is the Songs.SongID of the song we inserted
     */
    public function insertSong(array $song)
    {
        $sql = $this->create->insert()->into('Songs(Title, Duration)')
            ->values(':title, :duration')->result();
        $pdo = array('title' => $song['title'], 'duration' => $song['duration']);
        $this->database->run($sql, $pdo);

        return $this->database->getInsertId();
    }
}
