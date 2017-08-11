<?php

namespace Apps\Videos;

class PatchVideo
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->insert = new \Apps\Database\Insert();
        $this->update = new \Apps\Database\Update();
        $this->delete = new \Apps\Database\Delete();
    }

    public function patch(int $videoID, string $json)
    {
        $validator = new \Apps\Utils\Json();
        if ($validator->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $video = json_decode($json, true);
        if (isset($video[0]) == false) {
            $video = array($video);
        }

        try {
            foreach ($video[0] as $column => $value) {
                switch ($column) {
                    case 'categories':
                        $this->updateCategories($videoID, $value);
                        break;
                    default:
                        $this->updateVideo($videoID, $column, $value);
                        break;
                }
            }
        } catch (\PDOException $ex) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid data';
            return $response;
        }

        $response['code'] = 200;
        $response['contents'] = array();

        return $response;
    }

    /**
     * Remove all existing tags and replace them with the patched data
     * @param int $videoID is the ID we look for
     * @param int[] $values are the new values
     */
    public function updateCategories(int $videoID, array $values)
    {
        $sql = $this->delete->delete()->from('VideosTags')->where('VideoID = :id')->result();
        $pdo = array('id' => $videoID);
        $this->database->run($sql, $pdo);

        foreach ($values as $category) {
            $sql = $this->insert->insert()->into('VideosTags(VideoID, VideoCategoryID)')
                ->values(':vid, :cid')->result();
            $pdo = array('vid' => $videoID, 'cid' => $category);
            $this->database->run($sql, $pdo);
        }
    }

    /**
     * Patch the given VideoID with the new data we rececived.
     * @param int $videoID is the ID we update
     * @param string $column is the column we update
     * @param string $value is the value we add
     */
    public function updateVideo(int $videoID, string $column, string $value)
    {
        $sql = $this->update->update('Videos')->set($column.' = :value')->where('VideoID = :id')
            ->result();
        $pdo = array('value' => $value, 'id' => $videoID);
        $this->database->run($sql, $pdo);
    }
}
