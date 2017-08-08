<?php

namespace Apps\Biography;

class PatchBiography
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->select = new \Apps\Database\Select();
        $this->update = new \Apps\Database\Update();
    }

    public function patch(string $json)
    {
        $validator = new \Apps\Utils\Json();
        if ($validator->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        // Patch might be a JSON Object or JSON Array. We'll flatten the array to an object
        $patch = json_decode($json, true);
        if (isset($patch[0])) {
            // Convert JSON Array to a flat object
            foreach ($patch as $item) {
                foreach ($item as $column => $value) {
                    $result[$column] = $value;
                }
            }
            $patch = $result;
        }

        // Get the latest ID
        $sql = $this->select->select('BiographyID')->from('Biography')->order('Created DESC')
            ->limit(1)->result();
        $pdo = array();
        $latest = intval($this->database->run($sql, $pdo)[0]['BiographyID']);

        try {
            foreach ($patch as $column => $value) {
                $sql = $this->update->update('Biography')->set($column.' = :value')
                    ->where('BiographyID = :id')->result();
                $pdo = array('value' => $value, 'id' => $latest);
                $this->database->run($sql, $pdo);
            }
        } catch (\PDOException $ex) {
            // Invalid column, usually
            $response['code'] = 400;
            $response['contents'] = 'Invalid data in patch';
            return $response;
        }

        $response['code'] = 204;
        $response['contents'] = array();
        $response['id'] = $latest;
        return $response;
    }
}
