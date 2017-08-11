<?php

namespace Apps\Songs;

class GetSongs extends \Apps\Abstraction\CRUD
{
    /**
     * Return the results from Songs table. If a SongID is provided, return the details of that
     * song.
     * @param int $songID is the optional songID to look for
     * @return array[] the query results
     */
    public function get(int $songID = null)
    {
        $sql = $this->read->select()->from('Songs')->result();
        $pdo = array();

        if (isset($songID)) {
            $sql = $this->read->select()->from('Songs')->where('SongID = :id')->result();
            $pdo = array('id' => $songID);
        }

        $result = $this->database->run($sql, $pdo);

        return $result;
    }
}
