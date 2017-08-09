<?php

namespace Apps\Videos;

class AddVideos
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->insert = new \Apps\Database\Insert();
    }

    public function add(string $json)
    {
        $validator = new \Apps\Utils\Json();
        if ($validator->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            $response['id'] = -1;
            return $response;
        }

        $video = json_decode($json, true);

        $videoIDs = isset($video[0]['title']) == false
            ? $videoID = $this->addOneVideo($video)
            : $videoID = $this->addMultipleVideos($video);

        $response['code'] = 201;
        is_array($videoIDs)
            ? $response['contents'] = 'Location: https://www.vortechmusic.com/api/1.0/videos'
            : $response['contents'] = 'Location: https://www.vortechmusic.com/api/1.0/videos/'.$videoID;
        $response['id'] = $videoID;

        return $response;
    }

    /**
     * Add one video and its categories to the database.
     * @param array $video contains the data we insert
     * @return int $videoID is the ID we inserted
     */
    public function addOneVideo(array $video)
    {
        // Add the video data
        $sql = $this->insert->insert()->into('Videos(Title, URL, Created)')
            ->values(':title, :url, NOW()')->result();
        $pdo = array('title' => $video['title'], 'url' => $video['url']);
        $this->database->run($sql, $pdo);
        $videoID = $this->database->getInsertId();

        // And the video tags
        foreach ($video['categories'] as $tag) {
            $sql = $this->insert->insert()->into('VideosTags(VideoID, VideoCategoryID)')
                ->values(':id, :cid')->result();
            $pdo = array('id' => $videoID, 'cid' => $tag);
            $this->database->run($sql, $pdo);
        }

        return intval($videoID);
    }

    /**
     * Add multiple videos (JSON Array) and get the array of the VideoIDs
     * @param array $videos is the array of video arrays we will insert to the DB
     * @return int[] $videoIDs the array of the VideoIDs we inserted
     */
    public function addMultipleVideos(array $videos)
    {
        $videoIDs = array();
        foreach ($videos as $video) {
            $videoID = $this->addOneVideo($video);
            $videoIDs[] = $videoID;
        }

        return $videoIDs;
    }
}
