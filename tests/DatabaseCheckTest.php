<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DatabaseCheckTest extends TestCase
{
    public function setUp()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->dbcheck = new \Apps\Utils\DatabaseCheck();

        // Add a test entry (to News)
        $this->json = '{"title": "UnitTestAdd", "contents": "UnitTest News", "categories": [1, 2]}';

        $testNews = new \Apps\News\AddNews();
        $response = $testNews->add($this->json);
        $this->testID = $response['id'];
    }

    public function tearDown()
    {
        $builder = new \Apps\Database\Delete();
        $sql = $builder->delete()->from('News')->where('NewsID = :id')->result();
        $pdo = array('id' => $this->testID);
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->dbcheck instanceof \Apps\Utils\DatabaseCheck);
    }

    public function testExistsWithValidID()
    {
        $result = $this->dbcheck->existsInTable('News', 'NewsID', $this->testID);

        $this->assertTrue($result);
    }

    public function testExistsWithInvalidID()
    {
        $result = $this->dbcheck->existsInTable('News', 'NewsID', -17);

        $this->assertFalse($result);
    }
}
