<?php

namespace Apps\Releases\People;

class GetPeople extends \Apps\Abstraction\CRUD
{
    /**
     * Get the results of people who appeared in the given release ID
     * @param int $releaseID Is the release we look for
     */
    public function get(int $releaseID)
    {
        $sql = $this->read->select()->from('ReleasePeople')
            ->joins('JOIN People ON People.PersonID = ReleasePeople.PersonID')
            ->where('ReleaseID = :id')->result();
        $pdo = array('id' => $releaseID);

        $response['contents'] = $this->database->run($sql, $pdo);
        $response['code'] = 200;

        return $response;
    }
}
