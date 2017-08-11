<?php

namespace Apps\Videos;

class GetVideos extends \Apps\Abstraction\CRUD
{
    public function get(int $videoID = null)
    {
        $contents = empty($videoID) ? $this->getAllVideos() : $this->getOneVideo($videoID);

        $response['code'] = 200;
        $response['contents'] = $contents;

        return $response;
    }

    public function getAllVideos()
    {
        $sql = $this->read->select('VideoID')->from('Videos')->result();
        $pdo = array();
        $result = $this->database->run($sql, $pdo);

        $allVideoIDs = $this->arrays->flattenArray($result, 'VideoID');

        $contents = array();
        foreach ($allVideoIDs as $videoID) {
            $contents[] = $this->getOneVideo($videoID);
        }

        return $contents;
    }

    public function getOneVideo(int $videoID)
    {
        $sql = $this->read->select()->from('Videos')->where('VideoID = :id')->limit(1)->result();
        $pdo = array('id' => $videoID);
        $video = $this->database->run($sql, $pdo);
        if (empty($video)) {
            return array();
        }
        // Remove unneeded surrounding array
        $video = $video[0];

        // Get tags
        $sql = $this->read->select('VideoCategoryID')->from('VideosTags')->where('VideoID = :id')
            ->result();
        $tagIDs = $this->database->run($sql, $pdo);

        $videoTags = array();
        foreach ($tagIDs as $tag) {
            $sql = $this->read->select('Category')->from('VideosCategories')->where('VideoCategoryID = :tag')
                ->result();
            $pdo = array('tag' => $tag['VideoCategoryID']);
            $videoTags[] = $this->database->run($sql, $pdo)[0]['Category'];
        }
        $video['categories'] = $videoTags;

        return $video;
    }
}
