<?php

namespace VortechAPI\Apps\Database;

/**
 * From: https://stackoverflow.com/a/32804402/1488445
 *
 * Use this class to generate SQL select query strings easily and cleanly. Eg.
 * $query = new BuildSelect();
 * $sql = $query->select('MyColumn')->from('News')->where('NewsID = :id')->limit(1)->result();
 * echo $sql; // SELECT MyColumn FROM News WHERE NewsID = :id LIMIT 1
*
 * The :id can be used in creating prepared statements with PDO
 */
class BuildInsert
{
    private $into;
    private $values;

    public function insert()
    {
        return $this;
    }

    public function into()
    {
        $this->into = func_get_args();
        return $this;
    }

    public function values()
    {
        $this->values = func_get_args();
        return $this;
    }

    public function result()
    {
        // INSERT INTO
        $query[] = 'INSERT INTO';
        if (empty($this->into)) {
            return 'Invalid query!';
        }
        $query[] = join(', ', $this->into);

        // VALUES
        if (empty($this->values)) {
            return 'Invalid query!';
        }
        $query[] = 'VALUES ('.join(', ', $this->values).')';

        return join(' ', $query);
    }
}
