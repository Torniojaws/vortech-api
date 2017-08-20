<?php

namespace Apps\Contacts;

class AddContacts extends \Apps\Abstraction\CRUD
{
    public function add(string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            $response['id'] = -1;
            return $response;
        }

        $data = json_decode($json, true);
        if (isset($data[0]) == false) {
            $data = array($data);
        }

        $sql = $this->create->insert()
            ->into('Contacts(Email, TechRider, InputList, Backline, Created)')
            ->values(':email, :tech, :input, :back, NOW()')->result();
        $pdo = array(
            'email' => $data[0]['email'],
            'tech' => $data[0]['techrider'],
            'input' => $data[0]['inputlist'],
            'back' => $data[0]['backline']
        );
        $this->database->run($sql, $pdo);
        $contactID = $this->database->getInsertId();

        $response['code'] = 201;
        $response['contents'] = 'Location: https://www.vortechmusic.com/api/1.0/contacts/'.$contactID;
        $response['id'] = $contactID;

        return $response;
    }
}
