<?php

namespace VortechAPI\Tests\Videos;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DeleteVideoTest extends TestCase
{
    public function setUp()
    {
        $this->delete = new \Apps\Videos\DeleteVideo();
        $this->videos = new \Apps\Videos\AddVideos();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->select = new \Apps\Database\Select();

        // Add a video
        $json = '{"title": "UnitTest Video", "url": "http://www.videosite.com/test", "categories": [1, 4, 5]}';
        $this->videos->add($json);

        // Get the video ID
        $sql = $this->select->select()->from('Videos')->where('Title LIKE :title')
            ->order('VideoID ASC')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->validVideoID = $this->database->run($sql, $pdo)[0]['VideoID'];
    }

    public function tearDown()
    {
        // Just in case :)
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Videos')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->delete instanceof \Apps\Videos\DeleteVideo);
    }

    public function testDeleteWorksWithValidID()
    {
        $response = $this->delete->delete($this->validVideoID);

        $check = new \Apps\Utils\DatabaseCheck();
        $exists = $check->existsInTable('Videos', 'VideoID', $this->validVideoID);

        $this->assertFalse($exists, 'PersonID exists in table! Should not.');
        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(204, $response['code'], 'Wrong response code');
    }

    public function testDeleteReturnsExpectedResponseWithInvalidID()
    {
        $response = $this->delete->delete(-26);

        $this->assertEquals($response['code'], 400);
    }
}
