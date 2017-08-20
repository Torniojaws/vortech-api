<?php

namespace Apps\Subscribers;

class PatchSubscribers extends \Apps\Abstraction\CRUD
{
    public function patch(int $subID, string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $patch = json_decode($json, true);
        if (isset($patch[0]) == false) {
            $patch = array($patch);
        }

        try {
            foreach ($patch[0] as $column => $value) {
                switch ($column) {
                    case 'email':
                        $sql = $this->update->update('Subscribers')
                            ->set('Email = :email, Updated = NOW()')->where('SubscriberID = :id')
                            ->result();
                        $pdo = array('email' => $value, 'id' => $subID);
                        $this->database->run($sql, $pdo);
                        break;
                    default:
                        // Skip, undefined column
                        break;
                }
            }
        } catch (\PDOException $ex) {
            // Unknown column usually
            $response['code'] = 400;
            $response['contents'] = 'Invalid patch';
        }

        $response['code'] = 200;
        $response['contents'] = array();

        return $response;
    }
}
