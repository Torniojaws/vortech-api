<?php

namespace VortechAPI\Tests\Videos;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddVideosTest extends TestCase
{
    public function setUp()
    {
        $this->videos = new \Apps\Videos\AddVideos();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->select = new \Apps\Database\Select();
        $this->arrays = new \Apps\Utils\Arrays();
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Videos')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->videos instanceof \Apps\Videos\AddVideos);
    }

    public function testAddingAVideoWorks()
    {
        $json = '{"title": "UnitTest Video", "url": "http://www.videosite.com/test", "categories": [1, 4, 5]}';
        $response = $this->videos->add($json);

        $sql = $this->select->select()->from('Videos')->where('VideoID = :id')->limit(1)->result();
        $pdo = array('id' => $response['id']);
        $query = $this->database->run($sql, $pdo);

        $sql = $this->select->select()->from('VideosTags')->where('VideoID = :id')->result();
        $tagResult = $this->database->run($sql, $pdo);

        $tags = $this->arrays->flattenArray($tagResult, 'VideoCategoryID');
        sort($tags);

        $this->assertFalse(empty($response), 'Got empty response');
        $this->assertEquals(201, $response['code'], 'Wrong response code');
        $this->assertTrue(array_key_exists('id', $response), 'Insert ID not found in response');
        $this->assertEquals('UnitTest Video', $query[0]['Title'], 'Entry was not found in database');

        $this->assertEquals(1, $tags[0], 'Wrong value in first tag');
        $this->assertEquals(4, $tags[1], 'Wrong value in second tag');
    }

    public function testAddingMultipleVideosWithJSONArrayWorks()
    {
        $json = '[
            {"title": "UnitTest Video", "url": "http://www.videosite.com/test", "categories": [1, 4, 5]},
            {"title": "UnitTest Video Too", "url": "http://www.anothersite.com/video", "categories": [2, 7]}
        ]';
        $response = $this->videos->add($json);

        $sql = $this->select->select()->from('Videos')->where('Title LIKE :title')
            ->order('VideoID ASC')->result();
        $pdo = array('title' => 'UnitTest%');
        $videos = $this->database->run($sql, $pdo);

        $sql = $this->select->select()->from('VideosTags')->where('VideoID = :id')
            ->order('VideoCategoryID ASC')->result();
        $pdo = array('id' => $videos[0]['VideoID']);
        $tagsVideo1 = $this->database->run($sql, $pdo);

        $sql = $this->select->select()->from('VideosTags')->where('VideoID = :id')
            ->order('VideoCategoryID ASC')->result();
        $pdo = array('id' => $videos[1]['VideoID']);
        $tagsVideo2 = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'Got empty response');
        $this->assertEquals(201, $response['code'], 'Wrong response code');
        $this->assertTrue(array_key_exists('id', $response), 'Insert ID not found in response');

        // Video 1
        $this->assertEquals('UnitTest Video', $videos[0]['Title'], 'Video 1 was not found');
        $this->assertEquals(4, $tagsVideo1[1]['VideoCategoryID'], 'Video 1 has unexpected tag');

        // Video 2
        $this->assertEquals('UnitTest Video Too', $videos[1]['Title'], 'Video 2 was not found');
        $this->assertEquals(2, $tagsVideo2[0]['VideoCategoryID'], 'Video 2 has unexpected tag');
    }

    public function testAddingInvalidJSONIsBadRequest()
    {
        $json = '{invalid';
        $response = $this->videos->add($json);

        $this->assertEquals(400, $response['code']);
    }
}
