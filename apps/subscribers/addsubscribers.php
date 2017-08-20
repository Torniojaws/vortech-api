<?php

namespace Apps\Subscribers;

class AddSubscribers extends \Apps\Abstraction\CRUD
{
    public function add(string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $data = json_decode($json, true);
        if (isset($data[0]) == false) {
            $data = array($data);
        }

        // There can only be one (...) subscriber, but for JSON array support we do it like this
        foreach ($data[0] as $email) {
            $sql = $this->create->insert()->into('Subscribers(Email, UniqueID, Active, Created)')
                ->values(':email, :uuid, 1, NOW()')->result();
            // The UniqueID is used to authenticate unsubscribe and email address changes
            $pdo = array(
                'email' => $email,
                'uuid' => uniqid('', true)
            );

            // Check does the email already exist. If so, we will reactivate it instead of creating
            // a new entry.
            $exists = $this->dbCheck->existsInTable('Subscribers', 'Email', $email);
            if ($exists == true) {
                $sql = $this->update->update('Subscribers')->set('Active = 1')
                    ->where('Email = :email')->result();
                $pdo = array('email' => $email);
            }

            $this->database->run($sql, $pdo);

            // Get the ID of the new insert or the existing email
            $subID = $this->database->getInsertId();
            if ($exists == true) {
                $sql = $this->read->select('SubscriberID')->from('Subscribers')
                    ->where('Email = :email')->result();
                $pdo = array('email' => $email);
                $result = $this->database->run($sql, $pdo);
                $subID = $result[0]['SubscriberID'];
            }
        }

        $response['code'] = 201;
        $response['contents'] = 'Location: https://www.vortechmusic.com/api/1.0/subscribers/'.$subID;
        $response['id'] = $subID;

        return $response;
    }
}
