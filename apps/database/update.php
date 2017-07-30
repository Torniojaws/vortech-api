<?php

namespace Apps\Database;

class Update
{
    private $update = array();
    private $set;
    private $where;

    public function update(string $table = null)
    {
        $this->update = $table;
        if (empty($this->update)) {
            return 'Update query missing target table';
        }
        return $this;
    }

    public function set()
    {
        $this->set = func_get_args();
        if (empty($this->set)) {
            return 'Update query missing values';
        }
        return $this;
    }

    public function where($conditions = null)
    {
        $this->where = $conditions;
        return $this;
    }

    public function result()
    {
        // UPDATE
        $query[] = 'UPDATE';
        $query[] = $this->update;

        // SET
        $query[] = 'SET';
        $query[] = join(', ', $this->set);

        // WHERE (optional, but recommended)
        if (empty($this->where) == false) {
            $query[] = 'WHERE';
            $query[] = $this->where;
        }

        return join(' ', $query);
    }
}
