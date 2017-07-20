<?php

namespace Apps\Releases;

class PatchRelease
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();
    }

    /**
     * We must rely that the user provides correct values for the table columns
     */
    public function patch($releaseID, $json)
    {
        try {
            $items = json_decode($json, true);

            if ($this->releaseIDExists($releaseID) == false) {
                $response['contents'] = 'Unknown release ID';
                $response['code'] = 400;
                return $response;
            }

            $sqlBuilder = new \Apps\Database\Update();
            foreach ($items as $column => $value) {
                $sql = $sqlBuilder->update('Releases')->set($column.' = :value')
                    ->where('ReleaseID = :id')->result();
                $pdo = array('value' => $value, 'id' => $releaseID);
                $this->database->run($sql, $pdo);
            }

            $response['contents'] = null;
            $response['code'] = 204;
        } catch (\PDOException $exception) {
            // Most likely when column does not exist
            $response['contents'] = 'Patch failed. Columns OK? ID is OK?';
            $response['code'] = 400;
        }

        return $response;
    }

    public function releaseIDExists($releaseID)
    {
        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('COUNT(*) AS Count')->from('Releases')->where('ReleaseID = :id')
            ->limit(1)->result();
        $pdo = array('id' => $releaseID);
        $result = $this->database->run($sql, $pdo);
        $count = intval($result[0]['Count']);

        return $count > 0;
    }
}
