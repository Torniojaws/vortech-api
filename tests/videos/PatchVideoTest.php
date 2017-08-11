<?php

namespace VortechAPI\Tests\Videos;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchVideoTest extends TestCase
{
    public function setUp()
    {
        $this->videos = new \Apps\Videos\PatchVideo();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->select = new \Apps\Database\Select();
        $this->arrays = new \Apps\Utils\Arrays();

        // Add some videos for testing
        $insert = new \Apps\Database\Insert();

        // First video
        $sql = $insert->insert()->into('Videos(Title, URL, Created)')
            ->values(':title, :url, NOW()')->result();
        $pdo = array('title' => "UnitTesting", 'url' => 'https://m.youtube.com/watch?v=2zaq4haRrKY');
        $this->database->run($sql, $pdo);
        $this->validVideoID = $this->database->getInsertId();
        $this->validSongIDArray[] = $this->validVideoID;

        $sql = $insert->insert()->into('VideosTags(VideoID, VideoCategoryID)')
            ->values(':vid, :cid')->result();
        $pdo = array('vid' => $this->validVideoID, 'cid' => 1);
        $this->database->run($sql, $pdo);

        $sql = $insert->insert()->into('VideosTags(VideoID, VideoCategoryID)')
            ->values(':vid, :cid')->result();
        $pdo = array('vid' => $this->validVideoID, 'cid' => 2);
        $this->database->run($sql, $pdo);

        // Second video
        $sql = $insert->insert()->into('Videos(Title, URL, Created)')
            ->values(':title, :url, NOW()')->result();
        $pdo = array('title' => "UnitTest Video", 'url' => 'https://youtu.be/uuZSRkNEpz8');
        $this->database->run($sql, $pdo);
        $this->validSecondVideoID = $this->database->getInsertId();
        $this->validSongIDArray[] = $this->validSecondVideoID;

        // Tags for the video
        $sql = $insert->insert()->into('VideosTags(VideoID, VideoCategoryID)')
            ->values(':vid, :cid')->result();
        $pdo = array('vid' => $this->validSecondVideoID, 'cid' => 4);
        $this->database->run($sql, $pdo);

        $sql = $insert->insert()->into('VideosTags(VideoID, VideoCategoryID)')
            ->values(':vid, :cid')->result();
        $pdo = array('vid' => $this->validSecondVideoID, 'cid' => 5);
        $this->database->run($sql, $pdo);
    }

    public function tearDown()
    {
        $delete = new \Apps\Database\Delete();
        $sql = $delete->delete()->from('Videos')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->videos instanceof \Apps\Videos\PatchVideo);
    }

    public function testPatchingVideoWorks()
    {
        $json = '{"title": "UnitTest Patch", "categories": [5, 6]}';
        $response = $this->videos->patch($this->validVideoID, $json);

        $sql = $this->select->select('Title')->from('Videos')->where('VideoID = :id')->result();
        $pdo = array('id' => $this->validVideoID);
        $patched = $this->database->run($sql, $pdo)[0];

        $sql = $this->select->select('VideoCategoryID')->from('VideosTags')->where('VideoID = :id')
            ->result();
        $pdo = array('id' => $this->validVideoID);
        $taglist = $this->database->run($sql, $pdo);
        $tags = $this->arrays->flattenArray($taglist, 'VideoCategoryID');
        sort($tags);

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Patch', $patched['Title'], 'Title was not edited');
        $this->assertEquals(5, $tags[0], 'Tag 1 was not updated');
        $this->assertEquals(6, $tags[1], 'Tag 2 was not updated');
    }

    public function testPatchingVideoWorksWithJSONArray()
    {
        $json = '[{"title": "UnitTest Patch", "categories": [5, 6]}]';
        $response = $this->videos->patch($this->validVideoID, $json);

        $sql = $this->select->select('Title')->from('Videos')->where('VideoID = :id')->result();
        $pdo = array('id' => $this->validVideoID);
        $patched = $this->database->run($sql, $pdo)[0];

        $sql = $this->select->select('VideoCategoryID')->from('VideosTags')->where('VideoID = :id')
            ->result();
        $pdo = array('id' => $this->validVideoID);
        $taglist = $this->database->run($sql, $pdo);
        $tags = $this->arrays->flattenArray($taglist, 'VideoCategoryID');
        sort($tags);

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Patch', $patched['Title'], 'Title was not edited');
        $this->assertEquals(5, $tags[0], 'Tag 1 was not updated');
        $this->assertEquals(6, $tags[1], 'Tag 2 was not updated');
    }

    public function testInvalidDataIsBadRequest()
    {
        $json = '{invalid';
        $response = $this->videos->patch($this->validVideoID, $json);

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(400, $response['code'], 'Wrong response code');
    }
}
