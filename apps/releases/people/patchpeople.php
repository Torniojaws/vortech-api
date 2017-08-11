<?php

namespace Apps\Releases\People;

class PatchPeople extends \Apps\Abstraction\CRUD
{
    /**
     * The contents should only be a single patch JSON.
     * @param int $releaseID is the release to update
     * @param json $json is the JSON we use to patch the data
     * @return array $response Contains the response we want to send
     */
    public function patch(int $releaseID, string $json)
    {
        $patch = json_decode($json, true);
        if (isset($patch[1])) {
            $response['contents'] = 'Cannot patch two items at the same time';
            $response['code'] = 400;
            return $response;
        }

        $this->patchInstruments($patch);

        $response['contents'] = 'Location: http://www.vortechmusic.com/api/1.0/releases/'.$releaseID.'/people';
        $response['code'] = 200;

        return $response;
    }

    /**
     * Update the instruments the person played on the album
     * @param array $data Contains the updated info
     */
    public function patchInstruments(array $data)
    {
        $personID = $this->getPersonID($data['name']);

        $sql = $this->update->update('ReleasePeople')->set('Instruments = :inst')
            ->where('PersonID = :id')->result();
        $pdo = array('inst' => $data['instruments'], 'id' => $personID);
        $this->database->run($sql, $pdo);
    }

    /**
     * Get the PersonID using the name of the person
     * @param string $name is the person's name we look for
     * @return int The PersonID or int(0) if not found
     */
    public function getPersonID(string $name)
    {
        $sql = $this->read->select('ReleasePeople.PersonID')->from('ReleasePeople')
            ->joins('JOIN People ON People.PersonID = ReleasePeople.PersonID')
            ->where('People.Name = :name')->limit(1)->result();
        $pdo = array('name' => $name);
        $result = $this->database->run($sql, $pdo);

        return isset($result[0]) ? intval($result[0]['PersonID']) : 0;
    }
}
