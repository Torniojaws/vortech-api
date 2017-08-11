<?php

namespace Apps\People;

class AddPeople extends \Apps\Abstraction\CRUD
{
    public function add(string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $data = json_decode($json, true);
        $inserted = array();
        foreach ($data as $person) {
            // This allows us to use both a single object which becomes a string,
            // and an associative array with 'name' as key
            $name = $person;
            if (is_array($person)) {
                $name = $person['name'];
            }

            $sql = $this->create->insert()->into('People(Name)')->values(':name')->result();
            $pdo = array('name' => $name);
            $this->database->run($sql, $pdo);
            $inserted[] = $this->database->getInsertId();
        }

        $response['code'] = 201;
        $response['ids'] = $inserted;
        $response['contents'] = 'Location: http://www.vortechmusic.com/api/1.0/people';

        return $response;
    }
}
