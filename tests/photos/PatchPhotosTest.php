<?php

namespace VortechAPI\Tests\Photos;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchPhotosTest extends TestCase
{
    public function setUp()
    {
        $this->photos = new \Apps\Photos\PatchPhotos();

        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->dbCheck = new \Apps\Utils\DatabaseCheck();

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
        $this->assertTrue($this->photos instanceof \Apps\Photos\PatchPhotos);
    }

    public function testPatchingASimpleCaseWithJSONObjectWorks()
    {
        $json = '{"caption": "UnitTest Patched"}';
        $response = $this->photos->patch($this->validID, $json);

        $sql = $this->read->select()->from('Photos')->where('PhotoID = :id')->result();
        $pdo = array('id' => $this->validID);
        $patched = $this->database->run($sql, $pdo)[0];

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Patched', $patched['Caption'], 'Patch did not work');
    }

    public function testPatchingAComplexCaseWithJSONObject()
    {
        $json = '{"caption": "UnitTest Patches", "country": "Sweden", "countryCode": "SE",
            "album": "UnitTest Something New", "categories": [1, 3, 5]}';
        $response = $this->photos->patch($this->validID, $json);

        $sql = $this->read->select()->from('Photos')->where('PhotoID = :id')->result();
        $pdo = array('id' => $this->validID);
        $patched = $this->database->run($sql, $pdo)[0];

        $patchedAlbumExists = $this->dbCheck->existsInTable('PhotoAlbums', 'Title', 'UnitTest Something New');

        $sql = $this->read->select('PhotoCategoryID')->from('PhotoCategoryMapping')
            ->where('PhotoID = :id')->order('PhotoID ASC')->result();
        $result = $this->database->run($sql, $pdo);
        $patchedCategories = $this->arrays->flattenArray($result, 'PhotoCategoryID');

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Patches', $patched['Caption'], 'Patch did not work');
        $this->assertEquals('Sweden', $patched['Country'], 'Wrong country');
        $this->assertEquals('SE', $patched['CountryCode'], 'Wrong country code');
        $this->assertTrue($patchedAlbumExists, 'Patch album was not added');
        $this->assertEquals(5, $patchedCategories[2], 'Category was not updated');
    }

    public function testPatchingWithAnArray()
    {
        $json = '[{"caption": "UnitTest Patchies", "country": "Norway"}]';
        $response = $this->photos->patch($this->validID, $json);

        $sql = $this->read->select()->from('Photos')->where('PhotoID = :id')->result();
        $pdo = array('id' => $this->validID);
        $patched = $this->database->run($sql, $pdo)[0];

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Patchies', $patched['Caption'], 'Caption Patch did not work');
        $this->assertEquals('Norway', $patched['Country'], 'Country Patch did not work');
    }
}
