<?php

namespace Apps\People;

class EditPeople
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->update = new \Apps\Database\Update();
    }

    public function edit(int $personID, string $json)
    {
        $validator = new \Apps\Utils\Json();
        if ($validator->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        // Since the endpoint is for a specific person, we will not allow a valid JSON with more
        // than one object
        $person = json_decode($json, true);
        if (count($person) > 1) {
            $response['code'] = 400;
            $response['contents'] = 'You are only allowed to edit one person!';
            return $response;
        }

        // When $person is an array of arrays, it was a JSON Array. Otherwise a JSON Object
        $person = empty($person[0]['name']) ? $person['name'] : $person[0]['name'];

        $sql = $this->update->update('People')->set('Name = :name')->where('PersonID = :id')->result();
        $pdo = array('name' => $person, 'id' => $personID);
        $this->database->run($sql, $pdo);

        $response['code'] = 200;
        $response['contents'] = array();

        return $response;
    }
}
