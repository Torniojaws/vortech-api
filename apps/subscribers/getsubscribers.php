<?php

namespace Apps\Subscribers;

class GetSubscribers extends \Apps\Abstraction\CRUD
{
    public function get(int $subID = null)
    {
        $sql = $this->read->select('Email, UniqueID')->from('Subscribers')->where('Active = 1')
            ->order('SubscriberID ASC')->result();
        $pdo = array();

        // When an ID is provided, we do not check if they are active or not
        if ($subID) {
            $sql = $this->read->select('Email, UniqueID')->from('Subscribers')
                ->where('SubscriberID = :id')->order('SubscriberID ASC')->result();
            $pdo = array('id' => $subID);
        }

        $response['code'] = 200;
        $response['contents'] = $this->database->run($sql, $pdo);

        return $response;
    }
}
