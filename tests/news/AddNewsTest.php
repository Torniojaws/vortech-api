<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddNewsTest extends TestCase
{
    public function setUp()
    {
        $this->news = new \Apps\News\AddNews();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->json = '{"title": "UnitTestNews", "contents": "UnitTest posting news", "categories": [1, 2]}';
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('News')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->news instanceof \Apps\News\AddNews);
    }

    public function testInsertingNewsWorks()
    {
        $data = json_decode($this->json, true);
        $title = $data['title'];
        $contents = $data['contents'];
        $insertedID = $this->news->insertNews($title, $contents);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Title')->from('News')->where('NewsID = :id')->limit(1)->result();
        $pdo = array('id' => $insertedID);
        $result = $this->database->run($sql, $pdo);
        $value = $result[0]['Title'];

        $expected = 'UnitTestNews';

        $this->assertEquals($expected, $value);
    }

    public function testInsertingCategoriesWorks()
    {
        $data = json_decode($this->json, true);
        $title = $data['title'];
        $contents = $data['contents'];
        $categories = $data['categories'];

        $newsID = $this->news->insertNews($title, $contents);

        foreach ($categories as $category) {
            $this->news->insertCategory($category, $newsID);
        }

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('CategoryID')->from('NewsCategories')
            ->where('NewsID = :id')->limit(1)->result();
        $pdo = array('id' => $newsID);
        $result = $this->database->run($sql, $pdo);
        $value = intval($result[0]['CategoryID']);

        $expected = 1;

        $this->assertEquals($expected, $value);
    }

    public function testResponse()
    {
        $testValue = 123;
        $response = $this->news->response($testValue);

        $expected = 'Location: http://www.vortechmusic.com/api/1.0/news/'.$testValue;

        $this->assertEquals($expected, $response['contents']);
    }

    public function testAddGivesResponse()
    {
        $response = $this->news->add($this->json);

        $this->assertEquals($response['code'], 201);
    }
}
