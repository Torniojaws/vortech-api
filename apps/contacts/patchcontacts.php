<?php

namespace Apps\Contacts;

class PatchContacts extends \Apps\Abstraction\CRUD
{
    public function patch(int $contactID, string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $patches = json_decode($json, true);
        if (isset($patches[0]) == false) {
            $patches = array($patches);
        }

        try {
            foreach ($patches[0] as $column => $value) {
                $sql = $this->update->update('Contacts')->set($column.' = :value')
                    ->where('ContactsID = :id')->result();
                $pdo = array('value' => $value, 'id' => $contactID);
                $this->database->run($sql, $pdo);
            }
        } catch (\PDOException $ex) {
            // Invalid column usually
            $response['code'] = 400;
            $response['contents'] = 'Could not update. Columns correct?';
            return $response;
        }

        $response['code'] = 200;
        $response['contents'] = array();

        return $response;
    }
}
