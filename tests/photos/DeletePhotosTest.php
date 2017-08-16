<?php

namespace VortechAPI\Tests\Shop;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DeletePhotosTest extends TestCase
{
    public function setUp()
    {
        $this->photos = new \Apps\Photos\DeletePhotos();

        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->arrays = new \Apps\Utils\Arrays();
        $this->dbCheck = new \Apps\Utils\DatabaseCheck();

        // Add a photo
        $json = '{"image": "unittest.jpg", "caption": "UnitTest Photo", "takenBy": "UnitTester",
            "country": "Finland", "countryCode": "FI", "city": "Espoo", "album": "UnitTest Album",
            "categories": [1, 3]}';
        $photo = new \Apps\Photos\AddPhotos();
        $response = $photo->add($json);
        $this->validID = intval($response['id'][0]);
        $this->allValidIDs[] = $this->validID;

        // Add a second photo
        $json = '{"image": "unittest2.jpg", "caption": "UnitTest Photo 2", "takenBy": "UnitTester2",
            "country": "Finland", "countryCode": "FI", "city": "Espoo", "album": "UnitTest Album",
            "categories": [2, 3]}';
        $response = $photo->add($json);
        $this->anotherValidID = intval($response['id'][0]);
        $this->allValidIDs[] = $this->anotherValidID;
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
        $this->assertTrue($this->photos instanceof \Apps\Photos\DeletePhotos);
    }

    public function testDeletingWorks()
    {
        $response = $this->photos->delete($this->validID);

        $photoExists = $this->dbCheck->existsInTable('Photos', 'PhotoID', $this->validID);
        $catMappingExists = $this->dbCheck
            ->existsInTable('PhotoCategoryMapping', 'PhotoID', $this->validID);
        $albumMappingExists = $this->dbCheck
            ->existsInTable('PhotosAlbumsMapping', 'PhotoID', $this->validID);

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(204, $response['code'], 'Wrong response code');
        $this->assertFalse($photoExists, 'Photo was not deleted successfully');
        $this->assertFalse($catMappingExists, 'Category mappings were not deleted');
        $this->assertFalse($albumMappingExists, 'Album mappings were not deleted');
    }

    public function testDeletingNonExistingIDIs404()
    {
        $response = $this->photos->delete(-30);

        $this->assertFalse(empty($response), 'Empty response');
        $this->assertEquals(404, $response['code'], 'Wrong response code');
    }
}
