<?php

namespace VortechAPI\Tests\News;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetNewsTest extends TestCase
{
    public function setUp()
    {
        $this->news = new \Apps\News\GetNews();

        $this->add = new \Apps\Database\Insert();
        $this->remove = new \Apps\Database\Delete();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->news instanceof \Apps\News\GetNews);
    }

    public function testGettingAllNews()
    {
        $result = $this->news->get();

        $this->assertTrue(array_key_exists('contents', $result));
    }

    public function testGettingNewsByID()
    {
        // We need to add two entries to test getting by ID properly
        $this->buildTestData();

        // This gets the second ID that was created for the test case, so that we can be sure the
        // correct item is returned.
        $secondID = intval(end($this->currentIDs));
        $result = $this->news->get($secondID);

        $this->assertTrue($result['contents'][0]['Contents'] == 'Tester2');
    }

    public function tearDown()
    {
        $sql = $this->remove->delete()->from('News')
            ->where('Created >= NOW() - INTERVAL 1 MINUTE AND Title LIKE :value')->result();
        $pdo = array('value' => 'Test%');
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    private function buildTestData()
    {
        for ($i = 1; $i < 3; $i++) {
            $sql = $this->add->insert()->into('News(Title, Contents, Author, Created)')
                ->values(':title, :contents, :author, NOW()')->result();
            $pdo = array('title' => 'Test'.$i, 'contents' => 'Tester'.$i, 'author' => 'Testiman'.$i);
            $this->database->run($sql, $pdo);
            $this->currentIDs[] = $this->database->getInsertId();
        }
    }
}
