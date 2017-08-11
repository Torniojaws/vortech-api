<?php

namespace Apps\Shows;

class EditShow extends \Apps\Abstraction\CRUD
{
    public function edit(int $showID, string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $show = json_decode($json, true);

        $this->updateShow($showID, $show);
        $this->updateSetlist($showID, $show['setlist']);
        $this->updateBands($showID, $show['otherBands']);
        $this->updatePerformers($showID, $show['performers']);

        $response['id'] = $showID;
        $response['code'] = 200;
        $response['contents'] = array();

        return $response;
    }

    /**
     * Update the Shows table with the new data we received.
     * @param int $showID is the Show we will update
     * @param array $show contains the new data for the show
     */
    public function updateShow(int $showID, array $show)
    {
        $sql = $this->update->update('Shows')
            ->set('ShowDate = :date, CountryCode = :cc, Country = :country, City = :city, Venue = :venue')
            ->where('ShowID = :id')
            ->result();
        $pdo = array(
            'date' => $show['date'],
            'cc' => $show['countryCode'],
            'country' => $show['country'],
            'city' => $show['city'],
            'venue' => $show['venue'],
            'id' => $showID
        );
        $this->database->run($sql, $pdo);
    }

    /**
     * Update the ShowsSetlist table with the new data we received. It is possible the data contains
     * a song that does not exist in the table Songs yet, so that will be created if $song['title']
     * is a non-integer (= new song)
     * It is possible the user has forgot to use a valid ID for a song that was just added prior,
     * so we will also check by string comparison from the DB.
     * @param int $showID is the Show we will update
     * @param array $show contains the new data for the show
     */
    public function updateSetlist(int $showID, array $songs)
    {
        // First, we'll remove all Setlist entries for current ShowID. They will then be replaced
        $sql = $this->delete->delete()->from('ShowsSetlists')->where('ShowID = :id')->result();
        $pdo = array('id' => $showID);
        $this->database->run($sql, $pdo);

        $order = 0;
        foreach ($songs as $song) {
            $order++;
            $songID = $song;
            if (is_array($song)) {
                // Before adding the new song, check that it doesn't already exist with that name
                $exists = $this->dbCheck->existsInTable('Songs', 'Title', $song['title']);

                if ($exists == false) {
                    // Song does not exist. We will add it
                    $addSong = new \Apps\Songs\AddSongs();
                    $addSong->insertSong($song);
                }

                // Get the SongID
                $sql = $this->read->select('SongID')->from('Songs')->where('Title = :title')->result();
                $pdo = array('title' => $song['title']);
                $result = $this->database->run($sql, $pdo);
                $songID = $result[0]['SongID'];
            }

            // Add current song to the new setlist
            $sql = $this->create->insert()->into('ShowsSetlists(ShowID, SongID, SetlistOrder)')
                ->values(':show, :song, :order')->result();
            $pdo = array('show' => $showID, 'song' => $songID, 'order' => $order);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * Update the ShowsOtherBands table with the new data we received.
     * @param int $showID is the Show we will update
     * @param array $show contains the new data for the show
     */
    public function updateBands(int $showID, array $bands)
    {
        // Remove the old entries
        $sql = $this->delete->delete()->from('ShowsOtherBands')->where('ShowID = :id')->result();
        $pdo = array('id' => $showID);
        $this->database->run($sql, $pdo);

        // Then add the replacements
        foreach ($bands as $band) {
            $sql = $this->create->insert()->into('ShowsOtherBands(ShowID, BandName, BandWebsite)')
                ->values(':id, :name, :web')->result();
            $pdo = array('id' => $showID, 'name' => $band['name'], 'web' => $band['website']);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * Update the ShowsPeople table with the new data we received. It is possible that we get a
     * person that does not exist in the People table yet. They have a non-integer in
     * the property $person['personID']. They will be added to People table in that case.
     * @param int $showID is the Show we will update
     * @param array $show contains the new data for the show
     */
    public function updatePerformers(int $showID, array $people)
    {
        // First, we'll remove all ShowsPeople entries for current ShowID. They will then be replaced
        $sql = $this->delete->delete()->from('ShowsPeople')->where('ShowID = :id')->result();
        $pdo = array('id' => $showID);
        $this->database->run($sql, $pdo);

        // Then we add the new data
        foreach ($people as $person) {
            $personID = $person['personID'];
            if (is_numeric($person['personID']) == false) {
                // Before adding the new person, check that they don't already exist with that name
                $exists = $this->dbCheck->existsInTable('People', 'Name', $person['personID']);

                if ($exists == false) {
                    // Person does not exist. We will add them
                    $addPerson = new \Apps\People\AddPeople();
                    $addPerson->add(json_encode(array('name' => $person['personID'])));
                }

                // Get the PersonID
                $sql = $this->read->select('PersonID')->from('People')->where('Name = :name')->result();
                $pdo = array('name' => $personID);
                $result = $this->database->run($sql, $pdo);
                $personID = intval($result[0]['PersonID']);
            }

            // Add current person to the show performers
            $sql = $this->create->insert()->into('ShowsPeople(ShowID, PersonID, Instruments)')
                ->values(':show, :pid, :instruments')->result();
            $pdo = array('show' => $showID, 'pid' => $personID, 'instruments' => $person['instruments']);
            $this->database->run($sql, $pdo);
        }
    }
}
