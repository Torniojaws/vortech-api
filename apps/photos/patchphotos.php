<?php

namespace Apps\Photos;

class PatchPhotos extends \Apps\Abstraction\CRUD
{
    public function patch(int $photoID, string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $patches = json_decode($json, true);
        if (isset($patches[0]) == false) {
            $patches = array($patches);
        }

        // Only one array (= one PhotoID) is allowed
        foreach ($patches[0] as $column => $value) {
            switch ($column) {
                case 'album':
                    $this->updateAlbum($photoID, $value);
                    break;
                case 'categories':
                    $this->updateCategories($photoID, $value);
                    break;
                default:
                    $this->updatePhotos($photoID, $column, $value);
                    break;
            }
        }

        $response['code'] = 200;
        $response['contents'] = array();

        return $response;
    }

    public function updatePhotos(int $photoID, string $column, $value)
    {
        $sql = $this->update->update('Photos')->set($column.' = :value')->where('PhotoID = :id')->result();
        $pdo = array('value' => $value, 'id' => $photoID);
        $this->database->run($sql, $pdo);
    }

    /**
     * For now, we append the patched album. If it does not exist, we create it.
     * @param int $photoID is the ID
     * @param mixed $album Contains the new info. String = a new album. Int = existing reference
     */
    public function updateAlbum(int $photoID, $album)
    {
        // Reuse the same functionality from Add Photos class
        $photos = new \Apps\Photos\AddPhotos();
        $photos->addAlbum($photoID, $album);
    }

    /**
     * Append categories that have not yet been mapped to the current photo.
     * @param int $photoID is the ID we update
     * @param int[] $categories are the new values given
     */
    public function updateCategories(int $photoID, array $categories)
    {
        foreach ($categories as $category) {
            // Check that the current category has not been mapped to the photo yet
            $sql = $this->read->select('COUNT(*) AS Count')->from('PhotoCategoryMapping')
                ->where('PhotoID = :id AND PhotoCategoryID = :cat')->result();
            $pdo = array('id' => $photoID, 'cat' => $category);
            $count = intval($this->database->run($sql, $pdo)[0]['Count']);

            // If count is zero, we can add the current category to the mapping
            if ($count == 0) {
                $sql = $this->create->insert()->into('PhotoCategoryMapping(PhotoID, PhotoCategoryID)')
                    ->values(':pid, :cat')->result();
                $pdo = array('pid' => $photoID, 'cat' => $category);
                $this->database->run($sql, $pdo);
            }
        }
    }
}
