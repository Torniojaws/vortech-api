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
class Select
{
    private $select = array();
    private $from;
    private $joins;
    private $where;
    private $group;
    private $order;
    private $limit;

    public function select()
    {
        $this->select = func_get_args();
        return $this;
    }

    public function from(string $table)
    {
        $this->from = $table;
        return $this;
    }

    public function joins(string $joins)
    {
        $this->joins = $joins;
        return $this;
    }

    public function where(string $conditions)
    {
        $this->where = $conditions;
        return $this;
    }

    public function group()
    {
        $this->group = func_get_args();
        return $this;
    }

    public function order()
    {
        $this->order = func_get_args();
        return $this;
    }

    public function limit(string $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Build the full SQL string.
     * @return string $query The full SQL string that you can use
     */
    public function result()
    {
        // SELECT
        $query[] = 'SELECT';
        $query[] = empty($this->select) ? '*' : join(', ', $this->select);

        // FROM
        $query[] = 'FROM';
        $query[] = $this->from;

        // JOIN
        if (empty($this->joins) == false) {
            $query[] = $this->joins;
        }

        // WHERE
        if (empty($this->where) == false) {
            $query[] = 'WHERE';
            $query[] = $this->where;
        }

        // GROUP BY
        if (empty($this->group) == false) {
            $query[] = 'GROUP BY';
            $query[] = join(', ', $this->group);
        }

        // ORDER BY
        if (empty($this->order) == false) {
            $query[] = 'ORDER BY';
            $query[] = join(', ', $this->order);
        }

        // LIMIT
        if (empty($this->limit) == false) {
            $query[] = 'LIMIT';
            $query[] = $this->limit;
        }

        // Clear properties so that they don't appear in a second method call if they are not
        // changed.
        $old = get_object_vars($this);
        foreach (array_keys($old) as $key) {
            $this->$key = null;
        }

        return join(' ', $query);
    }
}
