<?php

namespace Apps\Contacts;

class GetContacts extends \Apps\Abstraction\CRUD
{
    public function get()
    {
        $sql = $this->read->select()->from('Contacts')->order('ContactsID DESC')->limit(1)->result();
        $pdo = array();

        $response['code'] = 200;
        $response['contents'] = $this->database->run($sql, $pdo);

        return $response;
    }
}
