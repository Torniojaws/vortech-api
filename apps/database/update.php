<?php

namespace Apps\Database;

class Update
{
    private $update = array();
    private $set;
    private $where;

    public function update($table)
    {
        $this->update = $table;
        return $this;
    }

    public function set()
    {
        $this->set = func_get_args();
        return $this;
    }

    public function where($conditions)
    {
        $this->where = $conditions;
        return $this;
    }

    public function result()
    {
        // UPDATE
        $query[] = 'UPDATE';
        if (empty($this->update)) {
            return 'Update query missing target table';
        }
        $query[] = $this->update;

        // SET
        $query[] = 'SET';
        if (empty($this->set)) {
            return 'Update query missing values';
        }
        $query[] = join(', ', $this->set);

        // WHERE (optional, but recommended)
        if (empty($this->where) == false) {
            $query[] = 'WHERE';
            $query[] = $this->where;
        }

        return join(' ', $query);
    }
}
