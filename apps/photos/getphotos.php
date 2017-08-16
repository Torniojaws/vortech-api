<?php

namespace Apps\Photos;

class GetPhotos extends \Apps\Abstraction\CRUD
{
    /**
     * Retrive photos from the DB. The actual files are in a predefined directory. We only reference
     * the actual filename for both the fullsize and the thumbnail photo.
     * @param int $photoID If given, we will only return matching data by Photos.PhotoID
     * @param int $category If given, we filter data by the category. If photoID is given, ignore.
     * @return array $response
     */
    public function get(int $photoID = null, int $categoryID = null)
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

            // Apply category filter
            if (empty($categoryID) == false && empty($photoID)) {
                // If photo is not in the category of the filter, we don't include it
                if (in_array($categoryID, $photo['categories']) == false) {
                    continue;
                }
            }
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
