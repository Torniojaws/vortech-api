<?php

namespace Apps\Videos;

class EditVideo
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->insert = new \Apps\Database\Insert();
        $this->update = new \Apps\Database\Update();
        $this->delete = new \Apps\Database\Delete();
    }

    public function edit(int $videoID, string $json)
    {
        $validator = new \Apps\Utils\Json();
        if ($validator->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $video = json_decode($json, true);
        // Convert JSON Object to Array, for easier processing
        if (isset($video[0]['title']) == false) {
            $video = array($video);
        }

        // Only one video can be edited
        if (count($video) > 1) {
            $response['code'] = 400;
            $response['contents'] = 'Too many items. Only 1 array is allowed.';
            return $response;
        }

        $this->updateVideo($videoID, $video[0]);
        $this->updateCategories($videoID, $video[0]['categories']);

        $response['code'] = 200;
        $response['contents'] = array();

        return $response;
    }

    /**
     * Update the Videos table with the new values
     * @param int $videoID the ID
     * @param array $video contains the data we add
     */
    public function updateVideo(int $videoID, array $video)
    {
        $sql = $this->update->update('Videos')
            ->set('Title = :title, URL = :url, Updated = NOW()')->where('VideoID = :id')->result();
        $pdo = array('title' => $video['title'], 'url' => $video['url'], 'id' => $videoID);
        $this->database->run($sql, $pdo);
    }

    /**
     * Delete the old values for VideoID and replace them with the new ones from the update
     * @param int $videoID is the ID we update
     * @param int[] $categories are the new values
     */
    public function updateCategories(int $videoID, array $categories)
    {
        $sql = $this->delete->delete()->from('VideosTags')->where('VideoID = :id')->result();
        $pdo = array('id' => $videoID);
        $this->database->run($sql, $pdo);

        foreach ($categories as $category) {
            $sql = $this->insert->insert()->into('VideosTags(VideoID, VideoCategoryID)')
                ->values(':vid, :cid')->result();
            $pdo = array('vid' => $videoID, 'cid' => $category);
            $this->database->run($sql, $pdo);
        }
    }
}
