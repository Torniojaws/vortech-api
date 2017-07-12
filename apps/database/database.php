<?php

// This is the gateway to the database features
class Database
{
    public function __construct()
    {
        $config = parse_ini_file('config_db.ini', true);
        $this->driver = $config['database']['driver'];
        $this->host = $config['database']['host'];
        $this->name = $config['database']['schema'];
        $this->charset = $config['database']['charset'];
        $this->user = $config['database']['username'];
        $this->pass = $config['database']['password'];
    }

    public function connect() {
        try {
            $this->pdo = new PDO(
                "$this->driver:host=$this->host; dbname=$this->name; charset=$this->charset",
                $this->user,
                $this->pass
            );
            // For added security with MySQL / MariaDB
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            // For Extra error details
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo $exception;
        }
    }

    public function run($statement, $params) {
        try {
            $this->query = $this->pdo->prepare($statement);
            if ($this->query->execute($params)) {
                $this->last_action_successful = true;
            } else {
                $this->last_action_successful = false;
            }
            // UPDATE, DELETE and INSERT queries will not return anything with fetch, so this
            // prevents showing a 2053 General Error
            if (substr($statement, 0, 6) == 'UPDATE'
                or substr($statement, 0, 6) == 'INSERT'
                or substr($statement, 0, 6) == 'DELETE'
            ) {
                return;
            }
            return $this->query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            echo $err;
        }
    }

    // This tells the ID in the database of the latest thing we added. Useful for updating Categories.
    public function getInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function close() {
        $this->pdo = null;
    }
}
