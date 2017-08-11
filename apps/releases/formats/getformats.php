<?php

namespace Apps\Releases\Formats;

class GetFormats extends \Apps\Abstraction\CRUD
{
    /**
     * Get the results of formats the ReleaseID has been released in
     * @param int $releaseID Is the release we look for
     */
    public function get(int $releaseID)
    {
        $sql = $this->read->select()->from('ReleaseFormats')
            ->joins('JOIN Formats ON ReleaseFormats.FormatID = Formats.FormatID')
            ->where('ReleaseID = :id')->result();
        $pdo = array('id' => $releaseID);

        $response['contents'] = $this->database->run($sql, $pdo);
        $response['code'] = 200;

        return $response;
    }
}
