<?php

namespace VortechAPI\Apps\Database;

/**
 * MySQL supports deleting from multiple tables at once. The syntax for that is:
 * DELETE t1, t2 FROM t1 INNER JOIN t2 INNER JOIN t3 WHERE t1.id=t2.id AND t2.id=t3.id;
 * It will be possible, but probably not used yet, so testing will be light.
 */
class BuildDelete
{
    private $delete = array();
    private $from;
    private $joins;
    private $where;

    public function delete()
    {
        $this->delete = func_get_args();
        return $this;
    }

    public function from($table)
    {
        $this->from = $table;
        return $this;
    }

    // Optional, but required when deleting from multiple tables
    public function joins($joins = null)
    {
        if (empty($this->delete) == false && $joins == null) {
            return 'When deleting from multiple tables, joins are required';
        }
        $this->joins = $joins;
        return $this;
    }

    public function where($conditions)
    {
        $this->where = $conditions;
        return $this;
    }

    public function result()
    {
        // DELETE
        $query[] = 'DELETE';
        if (empty($this->delete) == false) {
            $query[] = join(', ', $this->delete);
        }

        // FROM
        $query[] = 'FROM';
        if (empty($this->from)) {
            return 'Missing deletion target';
        }
        $query[] = $this->from;

        // JOINS (optional)
        // join type (inner, outer, etc.) should be chosen by user as part of the input
        if (empty($this->joins) == false) {
            $query[] = $this->joins;
        }

        // WHERE
        // This not officially required, but I will make it mandatory for safety reasons
        if (empty($this->where)) {
            return 'Using WHERE is mandatory in this API';
        }
        $query[] = 'WHERE';
        $query[] = $this->where;

        return join(' ', $query);
    }
}
