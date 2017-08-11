<?php

namespace Apps\Shows;

class GetShows extends \Apps\Abstraction\CRUD
{
    public function get(int $showID = null)
    {
        // Unless ShowID is given, we return ALL data
        $contents = empty($showID) ? $this->getAllShows() : $this->getOneShow($showID);

        $response['code'] = 200;
        $response['contents'] = empty($contents) ? array() : $contents;

        return $response;
    }

    /**
     * Return all shows as an array of single shows
     * @return array $results is the array of show arrays
     */
    public function getAllShows()
    {
        $sql = $this->read->select('ShowID')->from('Shows')->result();
        $pdo = array();
        $result = $this->database->run($sql, $pdo);

        $allShowIDs = $this->arrays->flattenArray($result, 'ShowID');

        $results = array();
        foreach ($allShowIDs as $showID) {
            $results[] = $this->getOneShow(intval($showID));
        }

        return $results;
    }

    /**
     * Return all data of a given ShowID in a nice array
     * @param int $showID is used to match the data from the three tables we use
     * @return array $result is the associative array of all data we have for a given show
     */
    public function getOneShow(int $showID)
    {
        // Get data from DB
        $show = $this->getShowData($showID);
        $setlist = $this->getSetlist($showID);
        $bands = $this->getOtherBands($showID);
        $players = $this->getPlayers($showID);

        // Build the array, which will then be turned into a JSON
        $result['showID'] = $show[0]['ShowID'];
        $result['date'] = $show[0]['ShowDate'];
        $result['countryCode'] = $show[0]['CountryCode'];
        $result['country'] = $show[0]['Country'];
        $result['city'] = $show[0]['City'];
        $result['venue'] = $show[0]['Venue'];
        $result['setlist'] = $this->getSongsList($setlist);
        $result['otherBands'] = $this->getBandsList($bands);
        $result['performers'] = $this->getPerformersList($players);

        return $result;
    }

    public function getShowData(int $showID)
    {
        $sql = $this->read->select()->from('Shows')->where('ShowID = :id')->result();
        $pdo = array('id' => $showID);
        return $this->database->run($sql, $pdo);
    }

    public function getSetlist(int $showID)
    {
        $sql = $this->read->select()->from('ShowsSetlists sl, Songs s')
            ->where('sl.SongID = s.SongID AND sl.ShowID = :id')->result();
        $pdo = array('id' => $showID);
        return $this->database->run($sql, $pdo);
    }

    public function getOtherBands(int $showID)
    {
        $sql = $this->read->select()->from('ShowsOtherBands')->where('ShowID = :id')->result();
        $pdo = array('id' => $showID);
        return $this->database->run($sql, $pdo);
    }

    public function getPlayers(int $showID)
    {
        $sql = $this->read->select()->from('ShowsPeople sp, People p')
            ->where('sp.ShowID = :id AND p.PersonID = sp.PersonID')->result();
        $pdo = array('id' => $showID);
        return $this->database->run($sql, $pdo);
    }

    public function getSongsList(array $setlist)
    {
        $songs = array();
        foreach ($setlist as $song) {
            $songs[] = array(
                'setlistOrder' => $song['SetlistOrder'],
                'songTitle' => $song['Title'],
                'duration' => $song['Duration']
            );
        }
        // Sort the array of arrays so that array[0] has the array with array['setlistOrder'] = 1
        // and array[1] is array['setlistOrder'] = 2, etc.
        // This is done with the new Spaceship operator <=> that was added to PHP7.
        // It does not exist in PHP5.x.
        usort($songs, function ($song1, $song2) {
            return $song1['setlistOrder'] <=> $song2['setlistOrder'];
        });
        return $songs;
    }

    public function getBandsList(array $bands)
    {
        $others = array();
        foreach ($bands as $band) {
            $others[] = array(
                'name' => $band['BandName'],
                'website' => $band['BandWebsite']
            );
        }
        return $others;
    }

    public function getPerformersList(array $players)
    {
        $performers = array();
        foreach ($players as $player) {
            $performers[] = array(
                'name' => $player['Name'],
                'instruments' => $player['Instruments']
            );
        }
        return $performers;
    }
}
