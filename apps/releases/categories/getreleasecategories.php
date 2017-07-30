<?php

namespace Apps\Releases\Categories;

class GetReleaseCategories
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->sql = new \Apps\Database\Select();
    }

    /**
     * Get the results of categories the ReleaseID has been released in
     * @param int $releaseID Is the release we look for
     */
    public function get(int $releaseID)
    {
        $sql = $this->sql->select()->from('ReleaseCategories')
            ->joins('JOIN ReleaseTypes ON ReleaseCategories.ReleaseTypeID = ReleaseTypes.ReleaseTypeID')
            ->where('ReleaseID = :id')->result();
        $pdo = array('id' => $releaseID);

        $response['contents'] = $this->database->run($sql, $pdo);
        $response['code'] = 200;
        return $response;
    }
}
