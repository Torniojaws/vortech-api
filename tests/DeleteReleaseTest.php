<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DeleteReleaseTest extends TestCase
{
    public function setUp()
    {
        $this->release = new \Apps\Releases\DeleteRelease();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        // Add the test album
        $this->json = '{"title": "UnitTestAdder", "date": "2017-07-19 12:00:00", "artist": "UnitTesties",
            "credits": "This is very welcome", "people": [{"id": 1, "name": "UnitTestExampler",
            "instruments": "Synths"},{"id": 2, "name": "UnitTestBoombastic", "instruments": "Drums"}],
            "songs": [{"title": "UnitTest My Song", "duration": 305}, {"title": "UnitTest Another Piece",
            "duration": 125}, {"title": "UnitTest Helppo", "duration": 201}], "categories": [1, 2],
            "formats": [1, 3]}';

        $testRelease = new \Apps\Releases\AddRelease();
        $response = $testRelease->add($this->json);
        $this->testReleaseID = $response['id'];
    }

    public function tearDown()
    {
        // Well, just in case...
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Releases')->where('ReleaseID = :id')->result();
        $pdo = array('id' => $this->testReleaseID);
        $this->database->run($sql, $pdo);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->release instanceof \Apps\Releases\DeleteRelease);
    }

    public function testDeleteWorksWithValidID()
    {
        $this->release->delete($this->testReleaseID);

        $check = new \Apps\Utils\DatabaseCheck();
        $exists = $check->existsInTable('Releases', 'ReleaseID', $this->testReleaseID);

        $this->assertFalse($exists);
    }

    public function testDeleteReturnsExpectedResponseWithInvalidID()
    {
        $response = $this->release->delete(-16);

        $this->assertEquals($response['code'], 400);
    }
}
