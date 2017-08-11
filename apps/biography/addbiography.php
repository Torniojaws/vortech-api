<?php

namespace Apps\Biography;

class AddBiography extends \Apps\Abstraction\CRUD
{
    public function add(string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $bio = json_decode($json, true);
        // Support both JSON Object and JSON Array
        if (isset($bio[0]['short']) == false) {
            $bio = array($bio);
        }

        $sql = $this->create->insert()->into('Biography(Short, Full, Created)')
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
