<?php

namespace Apps\Photos;

class AddPhotos extends \Apps\Abstraction\CRUD
{
    /**
     * Generally we receive a JSON array of multiple separate pictures to be added to the same
     * album. But we can also receive single pictures, or an array of unrelated pictures each going
     * to a different album. We check the key "album" - if it is a string, we create a new album,
     * otherwise it refers to an existing album and we will add the picture to that.
     * @param string $json contains the data we add
     */
    public function add(string $json)
    {
        if ($this->json->isJson($json) == false) {
            $response['code'] = 400;
            $response['contents'] = 'Invalid JSON';
            return $response;
        }

        $photos = json_decode($json, true);
        if (isset($photos[0]) == false) {
            $photos = array($photos);
        }

        $addedIDs = array();
        foreach ($photos as $photo) {
            // First add the photo, since we need the PhotoID for foreign keys
            $photoID = $this->addPhoto($photo);
            $addedIDs[] = $photoID;

            // Then add the album mappping and the category mapping
            $this->addAlbum($photoID, $photo['album']);
            $this->addCategories($photoID, $photo['categories']);
        }

        $response['code'] = 201;
        $response['contents'] = 'Location: https://www.vortechmusic.com/api/1.0/photos';
        $response['id'] = $addedIDs;
        return $response;
    }

    /**
     * Add a single photo to the Photos table.
     * @param array $photo has the data to add
     * @return int $photoID is the ID of the Photo we inserted
     */
    public function addPhoto(array $photo) {
        $sql = $this->create->insert()
            ->into('Photos(Image, Caption, TakenBy, Country, CountryCode, City, Created)')
            ->values(':img, :caption, :taken, :country, :cc, :city, NOW()')->result();
        $pdo = array(
            'img' => $photo['image'],
            'caption' => $photo['caption'],
            'taken' => $photo['takenBy'],
            'country' => $photo['country'],
            'cc' => $photo['countryCode'],
            'city' => $photo['city']
        );
        $this->database->run($sql, $pdo);

        return $this->database->getInsertId();
    }

    /**
     * Add the album mapping to the current photo. If "album" is a string, it is a new album that
     * we will add to the DB and then use the Insert ID as the album ID. Usually the album ID will
     * be an integer, which is the ID.
     * @param int $photoID is the ID of the photo
     * @param mixed $album contains the album info of the picture
     */
    public function addAlbum(int $photoID, $albumID)
    {
        if (is_numeric($albumID) == false) {
            // New album, but check does it already exist
            $exists = $this->dbCheck->existsInTable('PhotoAlbums', 'Title', $albumID);

            // If it exists, we get the ID. Otherwise we add it
            $albumID = $exists
                ? $this->getAlbumID($albumID)
                : $this->addPhotoAlbum($albumID);
        }

        $sql = $this->create->insert()->into('PhotosAlbumsMapping(PhotoID, AlbumID)')
            ->values(':photo, :album')->result();
        $pdo = array('photo' => $photoID, 'album' => $albumID);
        $this->database->run($sql, $pdo);
    }

    public function getAlbumID(string $albumTitle)
    {
        $sql = $this->read->select('AlbumID')->from('PhotoAlbums')->where('Title = :title')
            ->limit(1)->result();
        $pdo = array('title' => $albumTitle);
        return intval($this->database->run($sql, $pdo)[0]['AlbumID']);
    }

    public function addPhotoAlbum(string $albumTitle)
    {
        $sql = $this->create->insert()->into('PhotoAlbums(Title, Created)')
            ->values(':title, NOW()')->result();
        $pdo = array('title' => $albumTitle);
        $this->database->run($sql, $pdo);

        return $this->database->getInsertId();
    }

    /**
     * Add the photo categories mapping to DB. Non-integers are not allowed, ie. you cannot create
     * new categories via AddPhotos.
     * @param int $photoID is the ID of the photo
     * @param int[] $categories are the references
     */
    public function addCategories(int $photoID, array $categories)
    {
        foreach ($categories as $category) {
            $sql = $this->create->insert()->into('PhotoCategoryMapping(PhotoID, PhotoCategoryID)')
                ->values(':photo, :category')->result();
            $pdo = array('photo' => $photoID, 'category' => $category);
            $this->database->run($sql, $pdo);
        }
    }
}
