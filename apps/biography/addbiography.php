<?php

namespace Apps\Biography;

class AddBiography
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
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $bio = json_decode($json, true);
        // Support both JSON Object and JSON Array
        if (isset($bio[0]['short']) == false) {
            $bio = array($bio);
        }

        $sql = $this->insert->insert()->into('Biography(Short, Full, Created)')
            ->values(':short, :full, NOW()')->result();
        $pdo = array('short' => $bio[0]['short'], 'full' => $bio[0]['full']);
        $this->database->run($sql, $pdo);
        $bioID = $this->database->getInsertId();

        $response['code'] = 201;
        $response['contents'] = 'Location: https://www.vortechmusic.com/api/1.0/biography/'.$bioID;
        $response['id'] = $bioID;

        return $response;
    }
}
