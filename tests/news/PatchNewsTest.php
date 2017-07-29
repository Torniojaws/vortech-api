<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchNewsTest extends TestCase
{
    public function setUp()
    {
        $this->news = new \Apps\News\PatchNews();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->json = '{"title": "UnitTestNews", "contents": "UnitTestItem", "categories": [1, 2]}';

        // Add the test new
        $testNews = new \Apps\News\AddNews();
        $response = $testNews->add($this->json);
        $this->testNewsID = $response['id'];

        $this->select = new \Apps\Database\Select();
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('News')->where('NewsID = :id')->result();
        $pdo = array('id' => $this->testNewsID);
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->news instanceof \Apps\News\PatchNews);
    }

    public function testPatchingNewsEditsCorrectData()
    {
        $json = '{"contents": "UnitTestItem2"}';
        $this->news->patch($this->testNewsID, $json);

        $sql = $this->select->select('Contents')->from('News')->where('NewsID = :id')->limit(1)->result();
        $pdo = array('id' => $this->testNewsID);
        $result = $this->database->run($sql, $pdo);
        $text = $result[0]['Contents'];

        $expected = 'UnitTestItem2';
        $this->assertEquals($expected, $text);
    }

    public function testUpdateCategoriesWithValidData()
    {
        $test['categories'] = array(3, 4);
        $this->news->updateCategories($this->testNewsID, $test);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('CategoryID')->from('NewsCategories')->where('NewsID = :id')->result();
        $pdo = array('id' => $this->testNewsID);
        $result = $this->database->run($sql, $pdo);

        $categ[] = intval($result[0]['CategoryID']);
        $categ[] = intval($result[1]['CategoryID']);

        $this->assertEquals(array(3, 4), $categ);
    }

    public function testPatchingNewsWithCategoriesUpdatesNewsTable()
    {
        $json = '{"title": "UnitTestTitle2", "categories": [3, 4]}';
        $result = $this->news->patch($this->testNewsID, $json);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Title')->from('News')->where('NewsID = :id')->result();
        $pdo = array('id' => $this->testNewsID);
        $result = $this->database->run($sql, $pdo);
        $text = $result[0]['Title'];

        $expected = 'UnitTestTitle2';

        $this->assertEquals($expected, $text);
    }

    public function testPatchingNewsWithCategoriesUpdatesNewsCategoriesTable()
    {
        $json = '{"title": "UnitTestTitle2", "categories": [3, 4]}';
        $this->news->patch($this->testNewsID, $json);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('CategoryID')->from('NewsCategories')
            ->where('NewsID = :id AND CategoryID = 4')->result();
        $pdo = array('id' => $this->testNewsID);
        $result = $this->database->run($sql, $pdo);
        $value = intval($result[0]['CategoryID']);

        $expected = 4;

        $this->assertEquals($expected, $value);
    }

    public function testPatchingWithInvalidArray()
    {
        // Only int arrays are allowed
        $json = '{"title": "UnitTestTitle2", "categories": ["Patch mixed data, invalid category test", 4]}';
        $response = $this->news->patch($this->testNewsID, $json);

        $this->assertEquals($response['code'], 400);
    }

    public function testUpdateCategoriesWithInvalidArray()
    {
        // Only int arrays are allowed
        $json = '{"categories": [3, "UpdateCategories Invalid value test"]}';
        $test = json_decode($json, true);
        $success = $this->news->updateCategories($this->testNewsID, $test);

        $this->assertFalse($success);
    }

    public function testWithMissingNewsID()
    {
        $json = '{"title": "UnitTestTitle2", "categories": [2, 4]}';
        $response = $this->news->patch(-16, $json);

        $expected = 'Unknown news ID';

        $this->assertEquals($response['contents'], $expected);
    }
}
