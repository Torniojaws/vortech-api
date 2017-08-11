<?php

namespace Apps\Releases;

class PatchRelease extends \Apps\Abstraction\CRUD
{
    /**
     * We must rely that the user provides correct values for the table columns
     */
    public function patch(int $releaseID, string $json)
    {
        try {
            $items = json_decode($json, true);

            if ($this->dbCheck->existsInTable('Releases', 'ReleaseID', $releaseID) == false) {
                $response['contents'] = 'Unknown release ID';
                $response['code'] = 400;
                return $response;
            }

            foreach ($items as $column => $value) {
                $sql = $this->update->update('Releases')->set($column.' = :value')
                    ->where('ReleaseID = :id')->result();
                $pdo = array('value' => $value, 'id' => $releaseID);
                $this->database->run($sql, $pdo);
            }

            $response['contents'] = array();
            $response['code'] = 204;
        } catch (\PDOException $exception) {
            // Most likely when column does not exist
            $response['contents'] = 'Patch failed. Columns OK? ID is OK?';
            $response['code'] = 400;
        }

        return $response;
    }
}
