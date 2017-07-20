<?php

namespace Apps\Releases;

class DeleteRelease
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();
    }

    public function delete($releaseID)
    {
        if ($this->releaseIDExists($releaseID) == false) {
            $response['contents'] = 'Release not found';
            $response['code'] = 400;
            return $response;
        }

        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Releases')->where('ReleaseID = :id')->result();
        $pdo = array('id' => $releaseID);
        $this->database->run($sql, $pdo);
    }

    /**
     * Yes, this also exists in PatchRelease class, but since this is only used twice and nothing
     * else would need a toolset for things like this, I will not make a separate ReleaseTools class
     * yet. But if there's a second similar thing, this should be moved to ReleaseTools.
     */
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
