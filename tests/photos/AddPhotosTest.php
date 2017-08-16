<?php

namespace VortechAPI\Tests\Photos;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddPhotosTest extends TestCase
{
    public function setUp()
    {
        $this->photos = new \Apps\Photos\AddPhotos();

        $this->create = new \Apps\Database\Insert();
        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->arrays = new \Apps\Utils\Arrays();
    }

    public function tearDown()
    {
        $sql = $this->delete->delete()->from('Photos')->where('Caption LIKE :caption')->result();
        $pdo = array('caption' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $sql = $this->delete->delete()->from('PhotoAlbums')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->photos instanceof \Apps\Photos\AddPhotos);
    }

    public function testAddingOnePhotoWorks()
    {
        $json = '{"image": "unittest.jpg", "caption": "UnitTest Photo", "takenBy": "UnitTester",
            "country": "Finland", "countryCode": "FI", "city": "Espoo", "album": "UnitTest Album",
            "categories": [1, 3]}';
        $response = $this->photos->add($json);

        $sql = $this->read->select()->from('Photos')->where('PhotoID = :id')->limit(1)->result();
        $pdo = array('id' => $response['id'][0]);
        $photos = $this->database->run($sql, $pdo)[0];

        $sql = $this->read->select('Title')->from('PhotoAlbums')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $albums = $this->database->run($sql, $pdo);

        $sql = $this->read->select('PhotoCategoryID')->from('PhotoCategoryMapping')
            ->where('PhotoID = :id')->order('PhotoCategoryID ASC')->result();
        $pdo = array('id' => $response['id'][0]);
        $categories = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(201, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Photo', $photos['Caption'], 'Wrong title found');
        $this->assertFalse(empty($albums), 'Album was not found');
        $this->assertEquals('UnitTest Album', $albums[0]['Title'], 'New album was not added');
        $this->assertEquals(1, $categories[0]['PhotoCategoryID'], 'Category 1 is not correct');
        $this->assertEquals(3, $categories[1]['PhotoCategoryID'], 'Category 2 is not correct');
    }

    public function testThatInvalidJSONIsBadRequest()
    {
        $json = '{invalid';
        $response = $this->photos->add($json);

        $this->assertFalse(empty($response), 'Response should not be empty');
        $this->assertEquals(400, $response['code'], 'Wrong response code');
    }

    public function testAddingMultiplePicturesToSameAlbumWorks()
    {
        $json = '[
            {
                "image": "unittest1.jpg", "caption": "UnitTest Photo 1", "takenBy": "UnitTester",
                "country": "Finland", "countryCode": "FI", "city": "Espoo", "album": "UnitTest Album",
                "categories": [1, 3]
            },
            {
                "image": "unittest2.jpg", "caption": "UnitTest Photo 2", "takenBy": "UnitTester",
                "country": "Finland", "countryCode": "FI", "city": "Espoo", "album": "UnitTest Album",
                "categories": [1, 3]
            },
            {
                "image": "unittest3.jpg", "caption": "UnitTest Photo 3", "takenBy": "UnitTester",
                "country": "Finland", "countryCode": "FI", "city": "Espoo", "album": "UnitTest Album",
                "categories": [1, 3]
            }
        ]';
        $response = $this->photos->add($json);

        $sql = $this->read->select()->from('Photos')->where('Caption LIKE :caption')->result();
        $pdo = array('caption' => 'UnitTest%');
        $photos = $this->database->run($sql, $pdo);

        $sql = $this->read->select('COUNT(*) AS Count')->from('PhotoCategoryMapping')
            ->where('PhotoCategoryID = 1')->result();
        $pdo = array();
        $amountOfPhotosC1 = $this->database->run($sql, $pdo)[0]['Count'];

        $sql = $this->read->select('COUNT(*) AS Count')->from('PhotoAlbums')
            ->where('Title = :title')->result();
        $pdo = array('title' => 'UnitTest Album');
        $amountOfAlbums = $this->database->run($sql, $pdo)[0]['Count'];

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(201, $response['code'], 'Wrong response code');
        $this->assertFalse(empty($photos), 'No photos found!');
        $this->assertEquals('unittest1.jpg', $photos[0]['Image'], 'First image was not added OK');
        $this->assertEquals('UnitTest Photo 2', $photos[1]['Caption'], 'Second photo was not added OK');
        $this->assertEquals(3, $amountOfPhotosC1, 'Category 1 does not have all photos');
        $this->assertEquals(3, count($photos), 'Wrong amount of photos');
        $this->assertEquals(1, $amountOfAlbums, 'Album was added too many times');
    }

    public function testAddingMultiplePicturesToDifferentAlbumsWorks()
    {
        // Create a photo album that we refer to in the third Object
        $sql = $this->create->insert()->into('PhotoAlbums(Title, Created)')
            ->values(':title, NOW()')->result();
        $pdo = array('title' => 'UnitTest Existing');
        $this->database->run($sql, $pdo);
        $validAlbumID = $this->database->getInsertId();

        $json = '[
            {
                "image": "unittest1.jpg", "caption": "UnitTest Photo 1", "takenBy": "UnitTester",
                "country": "Finland", "countryCode": "FI", "city": "Espoo", "album": "UnitTest Album",
                "categories": [1, 3]
            },
            {
                "image": "unittest2.jpg", "caption": "UnitTest Photo 2", "takenBy": "UnitTester",
                "country": "Finland", "countryCode": "FI", "city": "Espoo", "album": "UnitTest Different",
                "categories": [1, 3]
            },
            {
                "image": "unittest3.jpg", "caption": "UnitTest Photo 3", "takenBy": "UnitTester",
                "country": "Finland", "countryCode": "FI", "city": "Espoo", "album": '.$validAlbumID.',
                "categories": [1, 3]
            }
        ]';
        $response = $this->photos->add($json);

        $sql = $this->read->select()->from('Photos')->where('PhotoID = :id')->limit(1)->result();
        $pdo = array('id' => $response['id'][2]);
        $photos = $this->database->run($sql, $pdo)[0];

        $sql = $this->read->select('Title')->from('PhotoAlbums')
            ->joins('JOIN PhotosAlbumsMapping map ON map.AlbumID = PhotoAlbums.AlbumID')
            ->order('PhotoAlbums.AlbumID ASC')->result();
        $pdo = array();
        $albums = $this->database->run($sql, $pdo);

        $sql = $this->read->select('Caption')->from('PhotoAlbums a')
            ->joins('JOIN PhotosAlbumsMapping map ON map.AlbumID = a.AlbumID
                     JOIN Photos p ON p.PhotoID = map.PhotoID')
            ->where('a.AlbumID = :aid')->result();
        $pdo = array('aid' => $validAlbumID);
        $existingAlbumPhoto = $this->database->run($sql, $pdo)[0];

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(201, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Photo 3', $photos['Caption'], 'Wrong title found');
        $this->assertEquals('UnitTest Existing', $albums[0]['Title'], 'Pre-existing album was not OK');
        $this->assertEquals('UnitTest Album', $albums[1]['Title'], 'First new album was not added OK');
        $this->assertEquals('UnitTest Different', $albums[2]['Title'], 'Second new album was not added OK');
        $this->assertFalse(empty($existingAlbumPhoto), 'No existing photo found');
        $this->assertEquals('UnitTest Photo 3', $existingAlbumPhoto['Caption'], 'Title was wrong');
    }
}
