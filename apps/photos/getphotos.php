<?php

namespace Apps\Photos;

class GetPhotos extends \Apps\Abstraction\CRUD
{
    public function get(int $photoID = null)
    {
        $sql = $this->read->select()->from('Photos')->order('PhotoID ASC')->result();
        $pdo = array();

        if ($photoID) {
            $sql = $this->read->select()->from('Photos')->where('PhotoID = :id')->limit(1)->result();
            $pdo = array('id' => $photoID);
        }

        $photos = $this->database->run($sql, $pdo);

        $contents = array();
        foreach ($photos as $photo) {
            $photo['album'] = $this->getPhotoAlbum($photo['PhotoID']);
            $photo['categories'] = $this->getPhotoCategories($photo['PhotoID']);
            $contents[] = $photo;
        }

        $response['code'] = 200;
        $response['contents'] = $contents;

        return $response;
    }

    public function getPhotoAlbum(int $photoID)
    {
        $sql = $this->read->select('a.AlbumID')->from('PhotoAlbums a')
            ->joins('JOIN PhotosAlbumsMapping map ON map.AlbumID = a.AlbumID')
            ->where('map.PhotoID = :id')->result();
        $pdo = array('id' => $photoID);
        $result = $this->database->run($sql, $pdo);
        return $this->arrays->flattenArray($result, 'AlbumID');
    }

    public function getPhotoCategories(int $photoID)
    {
        $sql = $this->read->select('c.PhotoCategoryID')->from('PhotoCategories c')
            ->joins('JOIN PhotoCategoryMapping map ON map.PhotoCategoryID = c.PhotoCategoryID')
            ->where('map.PhotoID = :id')->result();
        $pdo = array('id' => $photoID);
        $result = $this->database->run($sql, $pdo);
        return $this->arrays->flattenArray($result, 'PhotoCategoryID');
    }
}
