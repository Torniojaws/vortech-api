<?php

namespace Apps\Database;

// This is the gateway to the database features
class Database
{
    public function connect()
    {
        $config = $this->getConfig();

        $this->pdo = new \PDO(
            $config['driver'].":host=".$config['host']."; dbname=".$config['name']."; charset=".$config['charset'],
            $config['user'],
            $config['pass']
        );
        // For added security with MySQL / MariaDB
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        // For Extra error details
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function getConfig()
    {
        $data = parse_ini_file('config_db.ini', true);
        $config['driver'] = $data['database']['driver'];
        $config['host'] = $data['database']['host'];
        $config['name'] = $data['database']['schema'];
        $config['charset'] = $data['database']['charset'];
        $config['user'] = $data['database']['username'];
        $config['pass'] = $data['database']['password'];
        return $config;
    }

    public function run($statement, $params)
    {
        $this->query = $this->pdo->prepare($statement);

        if ($this->query->execute($params)) {
            $this->lastActionSuccessful = true;
        }

        // UPDATE, DELETE and INSERT queries will not return anything with fetch, so this
        // prevents showing a 2053 General Error
        $method = substr($statement, 0, 6);
        $skip = array('UPDATE', 'DELETE', 'INSERT');
        if (in_array($method, $skip)) {
            return;
        }
        return $this->query->fetchAll(\PDO::FETCH_ASSOC);
    }

    // This tells the ID in the database of the latest thing we added. Useful for updating Categories.
    public function getInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function close()
    {
        $this->pdo = null;
    }
}
