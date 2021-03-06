<?php

namespace Apps\Database;

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
class Insert
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
        if (empty($this->into)) {
            return 'Insert query missing target!';
        }
        return $this;
    }

    public function values()
    {
        $this->values = func_get_args();
        if (empty($this->values)) {
            return 'Insert query missing values!';
        }
        return $this;
    }

    public function result()
    {
        // INSERT INTO
        $query[] = 'INSERT INTO';
        $query[] = join(', ', $this->into);

        // VALUES
        $query[] = 'VALUES ('.join(', ', $this->values).')';

        // Clear properties so that they don't appear in a second method call if they are not
        // changed.
        $old = get_object_vars($this);
        foreach (array_keys($old) as $key) {
            $this->$key = null;
        }

        return join(' ', $query);
    }
}
