<?php

namespace Apps\Releases;

class AddRelease extends \Apps\Abstraction\CRUD
{
    /**
     * This is used to add new albums to the database. This is the main point of entry for this
     * functionality.
     * @param JSON $data This is the JSON we received in raw format
     * @return array The response array that we send back to the requester
     */
    public function add(string $data)
    {
        if ($this->json->isJson($data) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $json = json_decode($data, true);

        $releaseID = $this->insertRelease($json);
        // TODO: Move these to their own Add classes in the subdirs people, formats, etc
        $this->insertPeople($json);
        $this->insertReleasePeople($json, $releaseID);
        $this->insertSongs($json);
        $this->insertReleaseSongs($json, $releaseID);
        $this->insertReleaseFormats($json, $releaseID);
        $this->insertReleaseCategories($json, $releaseID);

        $response['contents'] = "Location: http://www.vortechmusic.com/api/1.0/releases/".$releaseID;
        $response['id'] = $releaseID;
        $response['code'] = 201;

        return $response;
    }

    /**
     * This is the main course that adds a new release. All other related data must be added
     * after this has been finished.
     * @param array $json This is the array version of the JSON we received
     * @return int Contains the ReleaseID we got from the DB
     */
    public function insertRelease(array $json)
    {
        $sql = $this->create->insert()->into('Releases(Title, Date, Artist, Credits, Created)')
            ->values(':title, :date, :artist, :credits, NOW()')->result();
        $pdo = array('title' => $json['title'], 'date' => $json['date'],
            'artist' => $json['artist'], 'credits' => $json['credits']);

        $this->database->run($sql, $pdo);

        return intval($this->database->getInsertId());
    }

    /**
     * After the Release has been added, we can insert the details on who performed on the album
     * and on which instrument. Due to the separation, we can easily have the same person play
     * different instruments on different albums.
     * @param array $json Contains the data we use to build the insert
     * @return boolean Done
     */
    public function insertPeople(array $json)
    {
        foreach ($json['people'] as $person) {
            if ($this->dbCheck->existsInTable('People', 'Name', $person['name']) == false) {
                $sql = $this->create->insert()->into('People(Name)')->values(':name')->result();
                $pdo = array('name' => $person['name']);
                $this->database->run($sql, $pdo);
            }
        }

        return true;
    }

    /**
     * This contains the per-album based information. Basically which person played which
     * instrument.
     * @param array $json The dataset from which we get the information
     * @param int $releaseID The ID of the release
     */
    public function insertReleasePeople(array $json, int $releaseID)
    {
        // Since this is a new release, there cannot be old data, so we simply insert all info
        foreach ($json['people'] as $person) {
            $currentPersonID = null;

            // Get the PersonID
            $sql = $this->read->select('PersonID')->from('People')->where('Name = :name')->result();
            $pdo = array('name' => $person['name']);
            $results = $this->database->run($sql, $pdo);
            if ($results) {
                $currentPersonID = intval($results[0]['PersonID']);
            }

            // Then let's insert the album people information
            $this->doInsertReleasePeople($releaseID, $currentPersonID, $person['instruments']);
        }
    }

    /**
     * Do the inserting of a release person. Make sure it is inserted. This is the per-release
     * insert, so it is always valid to insert since we are adding a new release.
     * @param int $releaseID is the release identifier which is used as a foreign key
     * @param int $personID tells who the person is
     * @param string $instruments This defines what instrument(s) the person played on this release
     * @return boolean Was the insert successful. If invalid ID, return false
     */
    public function doInsertReleasePeople(int $releaseID, int $personID, string $instruments)
    {
        $sql = $this->create->insert()->into('ReleasePeople(ReleaseID, PersonID, Instruments)')
            ->values(':rid, :pid, :instruments')->result();
        $pdo = array('rid' => $releaseID, 'pid' => $personID, 'instruments' => $instruments);
        $this->database->run($sql, $pdo);

        return $this->database->isQuerySuccessful();
    }

    /**
     * When a new release is added, the songs will be stored to the table Songs, if they do not
     * exist there already. They can exist if the new release is eg. a live album.
     * @param array $json Contains the actual data to use
     * @return boolean Were the songs inserted successfully
     */
    public function insertSongs(array $json)
    {
        $errors = 0;
        foreach ($json['songs'] as $song) {
            if ($this->dbCheck->existsInTable('Songs', 'Title', $song['title']) == false) {
                $sql = $this->create->insert()->into('Songs(Title, Duration)')
                    ->values(':title, :duration')->result();
                $pdo = array('title' => $song['title'], 'duration' => $song['duration']);
                $this->database->run($sql, $pdo);
            }
        }

        return $errors == 0;
    }

    /**
     * This contains the per-album based information. Basically which songs appear on which album.
     * @param array $json The dataset from which we get the information
     * @param int $releaseID The ID of the release
     */
    public function insertReleaseSongs(array $json, int $releaseID)
    {
        // Since this is a new release, there cannot be old data, so we simply insert all info
        foreach ($json['songs'] as $song) {
            $songID = null;

            // Get the SongID
            $sql = $this->read->select('SongID')->from('Songs')->where('Title = :title')->result();
            $pdo = array('title' => $song['title']);
            $results = $this->database->run($sql, $pdo);
            if ($results) {
                $songID = intval($results[0]['SongID']);
            }

            // Then let's insert the album people information
            $this->doInsertReleaseSongs($releaseID, $songID);
        }
    }

    /**
     * Do the inserting of a release song. Make sure it is inserted. This is the per-release
     * insert, so it is always valid to insert since we are adding a new release.
     * @param int $releaseID is the release identifier which is used as a foreign key
     * @param int $songID tells which song it is
     * @return boolean Was the insert successful. If invalid ID, return false
     */
    public function doInsertReleaseSongs(int $releaseID, int $songID)
    {
        $sql = $this->create->insert()->into('ReleaseSongs(ReleaseID, SongID)')
            ->values(':rid, :sid')->result();
        $pdo = array('rid' => $releaseID, 'sid' => $songID);
        $this->database->run($sql, $pdo);

        return $this->database->isQuerySuccessful();
    }

    /**
     * Each release can be assigned a selection of formats from a predefined list in table
     * Formats. This inserts the per-album based values.
     * @param array $json The data we use
     * @param int $releaseID The album identifier
     * @return boolean false when there is a missing releaseID
     */
    public function insertReleaseFormats(array $json, int $releaseID)
    {
        // It is an array of integers
        foreach ($json['formats'] as $format) {
            $value = intval($format);

            $sql = $this->create->insert()->into('ReleaseFormats(FormatID, ReleaseID)')
                ->values(':fid, :rid')->result();
            $pdo = array('fid' => $value, 'rid' => $releaseID);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * Each release can be assigned a selection of category types (though usually just one)
     * from a predefined list in table ReleaseTypes. This inserts the per-album based values.
     * @param array $json The data we use
     * @param int $releaseID The album identifier
     * @return boolean false When releaseID is missing
     */
    public function insertReleaseCategories(array $json, int $releaseID)
    {
        // It is an array of integers
        foreach ($json['categories'] as $category) {
            $value = intval($category);

            $sql = $this->create->insert()->into('ReleaseCategories(ReleaseID, ReleaseTypeID)')
                ->values(':rid, :tid')->result();
            $pdo = array('rid' => $releaseID, 'tid' => $value);
            $this->database->run($sql, $pdo);
        }
    }
}
