<?php

namespace Apps\Shows;

class AddShow
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->insert = new \Apps\Database\Insert();
    }

    public function add(string $json)
    {
        $validator = new \Apps\Utils\Json();
        if ($validator->isJson($json) == false) {
            $response['code'] = 400;
            $response['Contents'] = 'Invalid JSON';
            $response['id'] = null;
            return $response;
        }

        $data = json_decode($json, true);

        // We insert into four tables: Shows, ShowsSetlists, ShowsOtherBands, and ShowsPeople
        $this->insertShow($data);
        $showID = $this->database->getInsertId();
        $this->insertSetlist($showID, $data['setlist']);
        $this->insertBands($showID, $data['otherBands']);
        $this->insertPeople($showID, $data['performers']);

        $response['code'] = 201;
        $response['contents'] = 'Location: http://www.vortechmusic.com/api/1.0/shows/'.$showID;
        $response['id'] = $showID;

        return $response;
    }

    /**
     * Add the show to the database.
     * @param array $data Contains the values we will insert
     */
    public function insertShow(array $data)
    {
        $sql = $this->insert->insert()->into('Shows(ShowDate, CountryCode, Country, City, Venue)')
            ->values(':date, :cc, :country, :city, :venue')->result();
        $pdo = array(
            'date' => $data['date'],
            'cc' => $data['countryCode'],
            'country' => $data['country'],
            'city' => $data['city'],
            'venue' => $data['venue']
        );
        $this->database->run($sql, $pdo);
    }

    /**
     * The setlist is an array. If the value is an integer, it refers to Songs.SongID
     * If the value is an array, it is a new song that does not exist in Songs. In that case, we
     * add the new song into Songs table, and then to ShowsSetlists.
     * @param int $showID refers to the Show the setlist is connected to by foreign key
     * @param array $data has the songs we insert - either an int reference, or an array of new song data
     */
    public function insertSetlist(int $showID, array $data)
    {
        $songOrder = 0;
        foreach ($data as $song) {
            $songOrder++;
            $songID = (int)$song;
            if (is_array($song)) {
                // New song, add to Songs table
                $sql = $this->insert->insert()->into('Songs(Title, Duration)')
                    ->values(':title, :duration')->result();
                $pdo = array('title' => $song['title'], 'duration' => $song['duration']);
                $this->database->run($sql, $pdo);
                $songID = (int)$this->database->getInsertId();
            }

            $sql = $this->insert->insert()->into('ShowsSetlists(ShowID, SongID, SetlistOrder)')
                ->values(':sid, :song, :order')->result();
            $pdo = array('sid' => $showID, 'song' => $songID, 'order' => $songOrder);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * The other bands in the show will be inserted to their own table, along with a foreign key
     * reference to the Shows table.
     * @param int $showID is the show reference
     * @param array $data is the band data we insert
     */
    public function insertBands(int $showID, array $data)
    {
        foreach ($data as $band) {
            $sql = $this->insert->insert()->into('ShowsOtherBands(ShowID, BandName, BandWebsite)')
                ->values(':sid, :name, :web')->result();
            $pdo = array('sid' => $showID, 'name' => $band['name'], 'web' => $band['website']);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * Add the people who performed in the show, and what instruments they played in that show.
     * If performer PersonID is not an integer, it means that person is a new person. Probably
     * a guest performer in that show, or a new member that has only appeared live so far.
     * @param int $showID is the show we are adding
     * @param array $data contains the performer details. If PersonID is a string, it is a new person
     */
    public function insertPeople(int $showID, array $data)
    {
        foreach ($data as $person) {
            $currentPersonID = $person['personID'];
            if (is_numeric($currentPersonID) == false) {
                // Check that they don't exist already
                $dbCheck = new \Apps\Utils\DatabaseCheck();
                if ($dbCheck->existsInTable('People', 'Name', $currentPersonID) == false) {
                    // We have a new person, who will be added
                    $people = new \Apps\People\AddPeople();
                    $people->add(json_encode(array('name' => $currentPersonID)));
                }

                // Get the ID of the current new person (there might be several in one show)
                $select = new \Apps\Database\Select();
                $sql = $select->select('PersonID')->from('People')->where('Name = :name')->result();
                $pdo = array('name' => $currentPersonID);
                $result = $this->database->run($sql, $pdo);
                $currentPersonID = $result[0]['PersonID'];
            }

            $sql = $this->insert->insert()->into('ShowsPeople(ShowID, PersonID, Instruments)')
                ->values(':sid, :pid, :instruments')->result();
            $pdo = array(
                'sid' => $showID,
                'pid' => $currentPersonID,
                'instruments' => $person['instruments']
            );
            $this->database->run($sql, $pdo);
        }
    }
}
