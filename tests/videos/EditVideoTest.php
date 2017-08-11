<?php

namespace VortechAPI\Tests\Videos;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class EditVideoTest extends TestCase
{
    public function setUp()
    {
        $this->videos = new \Apps\Videos\EditVideo();

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
        $this->assertTrue($this->videos instanceof \Apps\Videos\EditVideo);
    }

    public function testEditingVideoWorks()
    {
        $json = '{"title": "UnitTest Edited", "url": "http://www.unittest.com/video", "categories": [3, 4]}';
        $response = $this->videos->edit($this->validVideoID, $json);

        $sql = $this->select->select()->from('Videos')->where('VideoID = :id')->order('VideoID ASC')
            ->limit(1)->result();
        $pdo = array('id' => $this->validVideoID);
        $query = $this->database->run($sql, $pdo)[0];

        $sql = $this->select->select('VideoCategoryID')->from('VideosTags')->where('VideoID = :id')
            ->order('VideoID ASC')->limit('')->result();
        $tagResult = $this->database->run($sql, $pdo);
        $tags = $this->arrays->flattenArray($tagResult, 'VideoCategoryID');
        sort($tags);

        $this->assertFalse(empty($response), 'Got empty response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertTrue(array_key_exists('contents', $response), 'Missing contents');
        $this->assertEquals('UnitTest Edited', $query['Title'], 'Wrong value in Title');
        $this->assertEquals(3, $tags[0], 'Wrong tag 1');
        $this->assertEquals(4, $tags[1], 'Wrong tag 2');
    }

    public function testEditingVideoWithArrayWorks()
    {
        $json = '[{"title": "UnitTest Edited", "url": "http://www.unittest.com/video", "categories": [3, 4]}]';
        $response = $this->videos->edit($this->validVideoID, $json);

        $sql = $this->select->select()->from('Videos')->where('VideoID = :id')->order('VideoID ASC')
            ->limit(1)->result();
        $pdo = array('id' => $this->validVideoID);
        $query = $this->database->run($sql, $pdo)[0];

        $sql = $this->select->select('VideoCategoryID')->from('VideosTags')->where('VideoID = :id')
            ->order('VideoID ASC')->limit('')->result();
        $tagResult = $this->database->run($sql, $pdo);
        $tags = $this->arrays->flattenArray($tagResult, 'VideoCategoryID');
        sort($tags);

        $this->assertFalse(empty($response), 'Got empty response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertTrue(array_key_exists('contents', $response), 'Missing contents');
        $this->assertEquals('UnitTest Edited', $query['Title'], 'Wrong value in Title');
        $this->assertEquals(3, $tags[0], 'Wrong tag 1');
        $this->assertEquals(4, $tags[1], 'Wrong tag 2');
    }

    public function testEditingWithMultipleArrayItemsIsBadRequest()
    {
        $json = '[
            {"title": "UnitTest 1", "url": "http://test.com", "categories": [1, 2]},
            {"title": "UnitTest 2", "url": "http://moretest.com", "categories": [2, 5]}
        ]';
        $response = $this->videos->edit($this->validVideoID, $json);

        $this->assertFalse(empty($response), 'Unexpected empty response');
        $this->assertEquals(400, $response['code'], 'Wrong response code');
    }

    public function testEditingWithInvalidJsonIsBadRequest()
    {
        $json = '{invalid';
        $response = $this->videos->edit($this->validVideoID, $json);

        $this->assertFalse(empty($response), 'Should not be empty');
        $this->assertEquals(400, $response['code'], 'Wrong response code');
    }
}
