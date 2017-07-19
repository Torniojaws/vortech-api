<?php

namespace Apps\Releases;

class AddRelease
{
    /**
     * This is used to add new albums to the database. This is the main point of entry for this
     * functionality.
     * @param JSON $data This is the JSON we received in raw format
     * @return array The response array that we send back to the requester
     */
    public function add($data)
    {
        $validator = new \Apps\Utils\Json();
        $dataIsValid = $validator->isJson($data);

        if ($dataIsValid) {
            $json = json_decode($data, true);

            $this->database = new \Apps\Database\Database();
            $this->database->connect();

            $this->insertRelease($json);
            $this->insertPeople($json);
            $this->insertReleasePeople($json);
        }

        $contents = "Location: http://www.vortechmusic.com/api/1.0/releases/".$this->currentReleaseID;

        return $this->buildResponse($dataIsValid, $contents);
    }

    /**
     * Build a proper response object based on the results
     * @param boolean $valid Tells if the JSON was valid
     * @param array $results Has the results from the INSERT queries
     * @return array $response Has the response array that will be converted to JSON later
     */
    public function buildResponse($valid, $results)
    {
        $response['contents'] = $results['release'];
        $response['code'] = 201;

        if ($valid == false) {
            $response['contents'] = 'Invalid JSON';
            $response['code'] = 400;
        }

        return $response;
    }

    /**
     * This is the main course that adds a new release. All other related data must be added
     * after this has been finished.
     * @param array $json This is the array version of the JSON we received
     * @return response Contains the results we got from the DB
     */
    public function insertRelease($json)
    {
        $sqlBuilder = new \Apps\Database\Insert();
        $sql = $sqlBuilder->insert()->into('Releases(Title, Date, Artist, Credits, Created)')
            ->values(':title, :date, :artist, :credits, NOW()')->result();
        $pdoParameters = array('title' => $json['title'], 'date' => $json['date'],
            'artist' => $json['artist'], 'credits' => $json['credits']);

        $this->database->run($sql, $pdoParameters);
        $this->currentReleaseID = $this->database->getInsertId();
    }

    /**
     * After the Release has been added, we can insert the details on who performed on the album
     * and on which instrument. Due to the separation, we can easily have the same person play
     * different instruments on different albums.
     * @param array $json Contains the data we use to build the insert
     */
    public function insertPeople($json)
    {
        // Check does each person exist. Add if current person doesn't.
        foreach ($json['people'] as $person) {
            if ($this->doesPersonExist($person['name']) == false) {
                $sqlBuilder = new \Apps\Database\Insert();
                $sql = $sqlBuilder->insert()->into('People(Name)')->values(':name')->result();
                $pdo = array('name' => $person['name']);
                $this->database->run($sql, $pdo);
            }
        }

    }

    /**
     * This is used to verify we can add the person as a new entry to the DB.
     * If he already exists, we will not add him.
     * @param string $name Is the full name of the person
     * @return boolean Whether the person exists or no
     */
    public function doesPersonExist($name)
    {
        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('COUNT(*)')->from('People')->where('Name = :name')->result();
        $pdo = array('name' => $name);
        $count = $this->database->run($sql, $pdo);

        return $count > 0;
    }

    /**
     * This contains the per-album based information. Basically which person played which
     * instrument.
     * @param array $json The dataset from which we get the information
     */
    public function insertReleasePeople($json)
    {
        $errors = 0;
        // Since this is a new release, there cannot be old data, so we simply insert all info
        foreach ($json['people'] as $person) {
            // Get the PersonID
            echo "Checking person=[".$person['name']."]";
            $get = new \Apps\Database\Select();
            $sql = $get->select('PersonID')->from('People')->where('Name = :name')->result();
            $pdo = array('name' => $person['name']);
            $currentPersonID = $this->database->run($sql, $pdo);

            // Then let's insert the album people information
            $sqlBuilder = new \Apps\Database\Insert();
            $sql = $sqlBuilder->insert()->into('ReleasePeople(ReleaseID, PersonID, Instruments)')
                ->values(':rid, :pid, :instruments')->result();
            $pdo = array('rid' => $this->currentReleaseID, 'pid' => $currentPersonID,
                'instruments' => $person['instruments']);
            print_r($this->database->getInsertId());
            $this->database->run($sql, $pdo);

            if (is_numeric($this->database->getInsertId()) == false) {
                $errors++;
            }
        }

        return $errors == 0;
    }
}
