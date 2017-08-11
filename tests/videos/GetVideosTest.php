<?php

namespace VortechAPI\Tests\Videos;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetVideosTest extends TestCase
{
    public function setUp()
    {
        $this->videos = new \Apps\Videos\GetVideos();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

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
        $this->assertTrue($this->videos instanceof \Apps\Videos\GetVideos);
    }

    public function testGettingVideosWithoutParamsGetsAllVideos()
    {
        $result = $this->videos->get();

        $this->assertFalse(empty($result), 'No results');
    }

    public function testGettingVideosWithASpecificIDThatExists()
    {
        $result = $this->videos->get($this->validVideoID);

        $this->assertFalse(empty($result));
        $this->assertEquals($this->validVideoID, $result['contents']['VideoID']);
    }

    public function testGettingVideosWithAnIDThatDoesNotExist()
    {
        $result = $this->videos->get(-28);

        $this->assertTrue(empty($result['contents']));
    }
}
