<?php

namespace Apps\Releases;

class EditRelease extends \Apps\Abstraction\CRUD
{
    /**
     * This will edit an existing release
     * @param int $releaseID
     * @param string $json The data in string JSON format
     * @return int $releaseID is the ID of the release we edited
     */
    public function edit(int $releaseID, string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $data = json_decode($json, true);
        $this->editRelease($releaseID, $data);
        $this->editReleasePeople($releaseID, $data);

        return $releaseID;
    }

    /**
     * This will modify the Releases table in the DB using the new values provided in $data
     * @param int $releaseID is the ID of the release to edit
     * @param array $release Contains the new values to use
     * @return boolean Was update query successful or no (even if nothing changes!)
     */
    public function editRelease(int $releaseID, array $release)
    {
        $sql = $this->update->update('Releases')
            ->set('Title = :title, Date = :date, Artist = :artist, Credits = :credits, Updated = NOW()')
            ->where('ReleaseID = :id')->result();
        $pdo = array('title' => $release['title'], 'date' => $release['date'], 'artist' => $release['artist'],
            'credits' => $release['credits'], 'id' => $releaseID);
        $this->database->run($sql, $pdo);

        return $this->database->isQuerySuccessful();
    }

    /**
     * By executive decision, you are only allowed to edit the instruments for a person on the album.
     * You cannot update their name as that would become messy to manage, and detecting the name change
     * would be hard. If a new non-existing person appears, it will be skipped. There will be a separate
     * endpoint for adding new people after-the-fact, and there you can assign them to a given existing
     * album.
     */
    public function editReleasePeople(int $releaseID, array $release)
    {
        foreach ($release['people'] as $person) {
            $this->editPerson($person, $releaseID);
        }
    }

    public function editPerson(array $person, int $releaseID)
    {
        $personID = $this->getPersonID($person['name']);

        $sql = $this->update->update('ReleasePeople')
            ->set('Instruments = :instruments')->where('ReleaseID = :id AND PersonID = :person')->result();
        $pdo = array('id' => $releaseID, 'instruments' => $person['instruments'], 'person' => $personID);
        $this->database->run($sql, $pdo);
    }

    public function getPersonID(string $name)
    {
        $sql = $this->read->select('PersonID')->from('People')->where('Name = :name')->result();
        $pdo = array('name' => $name);
        $result = $this->database->run($sql, $pdo);

        return intval($result[0]['PersonID']);
    }
}
