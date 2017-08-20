<?php

namespace Apps\Subscribers;

class DeleteSubscribers extends \Apps\Abstraction\CRUD
{
    public function delete(int $subID, string $uniqueID)
    {
        try {
            $sql = $this->update->update('Subscribers')->set('Active = 0')
                ->where('SubscriberID = :id AND UniqueID = :uuid')
                ->result();
            $pdo = array('id' => $subID, 'uuid' => $uniqueID);
            $this->database->run($sql, $pdo);

            $response['code'] = 204;
            $response['contents'] = array();
        } catch (\PDOException $ex) {
            $response['code'] = 500;
            $response['contents'] = 'Something went wrong when unsubscribing!';
        }

        return $response;
    }
}
