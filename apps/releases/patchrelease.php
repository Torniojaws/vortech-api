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
    public function patch(int $releaseID, string $json)
    {
        try {
            $items = json_decode($json, true);

            $check = new \Apps\Utils\DatabaseCheck();
            if ($check->existsInTable('Releases', 'ReleaseID', $releaseID) == false) {
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
}
