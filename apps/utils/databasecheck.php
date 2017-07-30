<?php

namespace Apps\Utils;

/**
 * This class will contain util methods for looking up various things from the database, mostly
 * for the endpoints to use. For example checking a NewsID, ReleaseID, etc. exists in their
 * respective table.
 */
class DatabaseCheck
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->query = new \Apps\Database\Select();
    }
    /**
     * Check whether Table contains Value in the given Column.
     * For example, in Table "News", does Column "NewsID" contain an entry with ID 1
     * @param string $table is the name of the table we check from
     * @param string $column is the name of the column we check from
     * @param string or int $value is the value we look for
     * @return boolean
     */
    public function existsInTable(string $table, string $column, $value)
    {
        $sql = $this->query->select('COUNT(*) AS Count')->from($table)->where($column.' = :value')
            ->limit(1)->result();
        $pdo = array('value' => $value);

        $result = $this->database->run($sql, $pdo);
        $count = intval($result[0]['Count']);

        return $count == 1;
    }
}
