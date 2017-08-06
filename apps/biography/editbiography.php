<?php

namespace Apps\Biography;

class EditBiography
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->select = new \Apps\Database\Select();
        $this->update = new \Apps\Database\Update();
    }
    public function edit(string $json)
    {
        $validator = new \Apps\Utils\Json();
        if ($validator->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $bio = json_decode($json, true);
        // Wrap in an array if decoded from JSON Object, so that we can handle both Array and Object
        if (isset($bio[0]['short']) == false) {
            $bio = array($bio);
        }

        // Get the latest ID
        $sql = $this->select->select('BiographyID')->from('Biography')->order('Created DESC')
            ->limit(1)->result();
        $pdo = array();
        $latest = intval($this->database->run($sql, $pdo)[0]['BiographyID']);

        // And update it
        $sql = $this->update->update('Biography')
            ->set('Short = :short, Full = :full, Updated = NOW()')->where('BiographyID = :id')
            ->result();
        $pdo = array('short' => $bio[0]['short'], 'full' => $bio[0]['full'], 'id' => $latest);
        $this->database->run($sql, $pdo);

        $response['code'] = 200;
        $response['contents'] = array();

        return $response;
    }
}
