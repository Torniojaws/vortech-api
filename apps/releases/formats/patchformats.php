<?php

namespace Apps\Releases\Formats;

class PatchFormats
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->insert = new \Apps\Database\Insert();
    }

    /**
     * The contents should only be a single patch JSON.
     * @param int $releaseID is the release to update
     * @param json $json is the JSON we use to patch the data
     * @return array $response Contains the response we want to send
     */
    public function patch($releaseID, $json)
    {
        $patch = json_decode($json, true);
        if (isset($patch['formats']) == false) {
            $response['contents'] = 'You can only patch formats in this endpoint';
            $response['code'] = 400;
            return $response;
        }

        $this->patchFormats($patch, $releaseID);

        $response['contents'] = 'Location: http://www.vortechmusic.com/api/1.0/releases/'.$releaseID.'/formats';
        $response['code'] = 200;
        return $response;
    }

    /**
     * Add new formats the album was released on
     * @param int[] $data Contains the new formats
     * @param int $releaseID is the release we will patch
     */
    public function patchFormats($data, $releaseID)
    {
        foreach ($data['formats'] as $format) {
            $sql = $this->insert->insert()->into('ReleaseFormats(FormatID, ReleaseID)')
                ->values(':format, :rid')->result();
            $pdo = array('format' => $format, 'rid' => $releaseID);
            $this->database->run($sql, $pdo);
        }
    }
}
