<?php

namespace Apps\Abstraction;

abstract class CRUD
{
    /**
     * This class is extended by pretty much all API endpoint classes.
     */
    public function __construct()
    {
        // Connection
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        // CRUD
        $this->create = new \Apps\Database\Insert();
        $this->read = new \Apps\Database\Select();
        $this->update = new \Apps\Database\Update();
        $this->delete = new \Apps\Database\Delete();

        // Utils
        $this->arrays = new \Apps\Utils\Arrays();
        $this->dbCheck = new \Apps\Utils\DatabaseCheck();
        $this->json = new \Apps\Utils\Json();
    }
}
