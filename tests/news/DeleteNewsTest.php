<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
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
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->news instanceof \Apps\News\DeleteNews);
    }

    public function testDeleteWorksWithValidID()
    {
        $this->news->delete($this->testNewsID);

        $check = new \Apps\Utils\DatabaseCheck();
        $result = $check->existsInTable('News', 'NewsID', $this->testNewsID);

        $this->assertFalse($result);
    }

    public function testDeleteReturnsExpectedResponseWithInvalidID()
    {
        $response = $this->news->delete(-16);

        $this->assertEquals($response['code'], 400);
    }
}
