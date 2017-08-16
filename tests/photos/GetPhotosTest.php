<?php

namespace VortechAPI\Tests\Photos;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetPhotosTest extends TestCase
{
    public function setUp()
    {
        $this->photos = new \Apps\Photos\GetPhotos();

        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->arrays = new \Apps\Utils\Arrays();

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
        $this->assertTrue($this->photos instanceof \Apps\Photos\GetPhotos);
    }

    public function testGettingAllPhotos()
    {
        $response = $this->photos->get();
        $photos = $response['contents'];

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals(2, count($photos), 'Wrong amount of photos received');
        $this->assertEquals('unittest2.jpg', $photos[1]['Image'], 'Unexpected filename');
        $this->assertFalse(empty($photos[0]['categories']), 'No categories found');
        $this->assertEquals(1, $photos[0]['categories'][0], 'Did not get category ID');
    }

    public function testGettingOnePhoto()
    {
        $response = $this->photos->get($this->validID);
        $photos = $response['contents'];

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals(1, count($photos), 'Wrong amount of photos received');
        $this->assertEquals('unittest.jpg', $photos[0]['Image'], 'Unexpected filename');
        $this->assertFalse(empty($photos[0]['categories']), 'No categories found');
        $this->assertEquals(3, $photos[0]['categories'][1], 'Did not get category ID');
    }
}
