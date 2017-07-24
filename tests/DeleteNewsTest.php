<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DeleteNewsTest extends TestCase
{
    public function setUp()
    {
        $this->news = new \Apps\News\DeleteNews();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        // Add the test news
        $this->json = '{"title": "UnitTestAdd", "contents": "UnitTest News", "categories": [1, 2]}';

        $testNews = new \Apps\News\AddNews();
        $response = $testNews->add($this->json);
        $this->testNewsID = $response['id'];
    }

    public function tearDown()
    {
        // Well, just in case...
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('News')->where('NewsID = :id')->result();
        $pdo = array('id' => $this->testNewsID);
        $this->database->run($sql, $pdo);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->news instanceof \Apps\News\DeleteNews);
    }

    public function testDeleteWorksWithValidID()
    {
        $this->news->delete($this->testNewsID);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('COUNT(*) AS Count')->from('News')
            ->where('NewsID = :id')->limit(1)->result();
        $pdo = array('id' => $this->testNewsID);
        $result = $this->database->run($sql, $pdo);
        $count = intval($result[0]['Count']);

        $this->assertTrue($count == 0);
    }

    public function testDeleteReturnsExpectedResponseWithInvalidID()
    {
        $response = $this->news->delete(-16);

        $this->assertEquals($response['code'], 400);
    }

    public function testNewsExistsUsingValidID()
    {
        $newsExists = $this->news->newsIDExists($this->testNewsID);

        $this->assertTrue($newsExists);
    }

    public function testNewsExistsUsingInvalidID()
    {
        $newsExists = $this->news->newsIDExists(-15);

        $this->assertFalse($newsExists);
    }
}
