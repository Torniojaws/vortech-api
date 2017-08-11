<?php

namespace Apps\Releases\Categories;

class PatchReleaseCategories extends \Apps\Abstraction\CRUD
{
    /**
     * The contents should only be a single patch JSON.
     * @param int $releaseID is the release to update
     * @param json $json is the JSON we use to patch the data
     * @return array $response Contains the response we want to send
     */
    public function patch(int $releaseID, string $json)
    {
        $patch = json_decode($json, true);
        if (isset($patch['categories']) == false) {
            $response['contents'] = 'You can only patch categories in this endpoint';
            $response['code'] = 400;
            return $response;
        }

        $this->patchCategories($patch, $releaseID);

        $response['contents'] = 'Location: http://www.vortechmusic.com/api/1.0/releases/'.$releaseID.'/categories';
        $response['code'] = 200;

        return $response;
    }

    /**
     * Add new categories the album was released on
     * @param int[] $data Contains the new categories
     * @param int $releaseID is the release we will patch
     */
    public function patchCategories(array $data, int $releaseID)
    {
        foreach ($data['categories'] as $category) {
            $sql = $this->create->insert()->into('ReleaseCategories(ReleaseID, ReleaseTypeID)')
                ->values(':rid, :category')->result();
            $pdo = array('rid' => $releaseID, 'category' => $category);
            $this->database->run($sql, $pdo);
        }
    }
}
