<?php

namespace VortechAPI\Tests\Releases;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetReleaseTest extends TestCase
{
    public function setUp()
    {
        $this->release = new \Apps\Releases\GetRelease();

        $this->add = new \Apps\Database\Insert();
        $this->remove = new \Apps\Database\Delete();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->release instanceof \Apps\Releases\GetRelease);
    }

    public function testGettingAllReleases()
    {
        $result = $this->release->get();

        $this->assertTrue(array_key_exists('contents', $result));
    }

    public function testGettingReleaseByID()
    {
        // We need to add two entries to test getting by ID properly
        $this->buildTestData();

        // This gets the second ID that was created for the test case, so that we can be sure the
        // correct item is returned.
        $secondID = intval(end($this->currentIDs));
        $result = $this->release->get($secondID);

        $this->assertTrue($result['contents'][0]['Artist'] == 'Tester2');
    }

    public function tearDown()
    {
        $sql = $this->remove->delete()->from('Releases')
            ->where('Date >= NOW() - INTERVAL 1 MINUTE AND Title LIKE :value')->result();
        $pdo = array('value' => 'Test%');
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    private function buildTestData()
    {
        for ($i = 1; $i < 3; $i++) {
            $sql = $this->add->insert()->into('Releases(Title, Date, Artist, Credits, Created)')
                ->values(':title, NOW(), :artist, :credits, NOW()')->result();
            $pdo = array('title' => 'Test'.$i, 'artist' => 'Tester'.$i, 'credits' => 'All music by Tester'.$i);
            $this->database->run($sql, $pdo);
            $this->currentIDs[] = $this->database->getInsertId();
        }
    }
}
