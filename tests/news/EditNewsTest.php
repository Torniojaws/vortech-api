<?php

namespace VortechAPI\Tests\News;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class EditNewsTest extends TestCase
{
    public function setUp()
    {
        $this->news = new \Apps\News\EditNews();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->json = '{"title": "UnitTestNews Edit", "contents": "UnitTest is fun", "categories": [1, 2]}';

        // Add the test news
        $testNews = new \Apps\News\AddNews();
        $response = $testNews->add($this->json);
        $this->testNewsID = $response['id'];
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('News')->where('NewsID = :id')->result();
        $pdo = array('id' => $this->testNewsID);
        $this->database->run($sql, $pdo);

        $sql = $sqlBuilder->delete()->from('NewsCategories')->where('NewsID = :id')->result();
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->news instanceof \Apps\News\EditNews);
    }

    public function testEditNewsWorks()
    {
        $json = '{"title": "UnitTestNews Edit2", "contents": "UnitTest is fun2", "categories": [2, 3]}';
        $array = json_decode($json, true);

        $this->news->editNews($this->testNewsID, $array);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Contents')->from('News')->where('NewsID = :id')->limit(1)->result();
        $pdo = array('id' => $this->testNewsID);
        $result = $this->database->run($sql, $pdo);

        $expected = 'UnitTest is fun2';

        $this->assertEquals($expected, $result[0]['Contents']);
    }

    public function testDeleteCategories()
    {
        $this->news->deleteCategories($this->testNewsID);

        $check = new \Apps\Utils\DatabaseCheck();
        $exists = $check->existsInTable('NewsCategories', 'NewsID', $this->testNewsID);

        $this->assertFalse($exists);
    }

    public function testUpdatingCategoryWorks()
    {
        $categoryID = 4;
        $this->news->addUpdatedCategory($categoryID, $this->testNewsID);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('COUNT(*) AS Count')->from('NewsCategories')
            ->where('NewsID = :id AND CategoryID = :value')->limit(1)->result();
        $pdo = array('id' => $this->testNewsID, 'value' => $categoryID);
        $result = $this->database->run($sql, $pdo);
        $count = intval($result[0]['Count']);

        $this->assertTrue($count == 1);
    }

    public function testNewsIsEdited()
    {
        $json = '{"title": "UnitTestNews Edit3", "contents": "UnitTest is fun3", "categories": [2, 3]}';
        $this->news->edit($this->testNewsID, $json);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Title')->from('News')->where('NewsID = :id')->limit(1)->result();
        $pdo = array('id' => $this->testNewsID);
        $result = $this->database->run($sql, $pdo);

        $expected = 'UnitTestNews Edit3';
        $this->assertEquals($expected, $result[0]['Title']);
    }

    public function testCategoriesAreEdited()
    {
        $json = '{"title": "UnitTestNews Edit3", "contents": "UnitTest is fun3", "categories": [2, 3]}';
        $this->news->edit($this->testNewsID, $json);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('COUNT(*) AS Count')->from('NewsCategories')
            ->where('NewsID = :id AND CategoryID = :cid')->limit(1)->result();
        $pdo = array('id' => $this->testNewsID, 'cid' => 3);
        $result = $this->database->run($sql, $pdo);
        $count = intval($result[0]['Count']);

        $this->assertTrue($count == 1);
    }
}
