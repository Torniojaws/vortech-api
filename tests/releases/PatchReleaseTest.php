<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchReleaseTest extends TestCase
{
    public function setUp()
    {
        $this->release = new \Apps\Releases\PatchRelease();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->json = '{"title": "UnitTestAdder", "date": "2017-07-19 12:00:00", "artist": "UnitTesties",
            "credits": "This is very welcome", "people": [{"id": 1, "name": "UnitTestExampler",
            "instruments": "Synths"},{"id": 2, "name": "UnitTestBoombastic", "instruments": "Drums"}],
            "songs": [{"title": "UnitTest My Song", "duration": 305}, {"title": "UnitTest Another Piece",
            "duration": 125}, {"title": "UnitTest Helppo", "duration": 201}], "categories": [1, 2],
            "formats": [1, 3]}';

        // Add the test album
        $testRelease = new \Apps\Releases\AddRelease();
        $response = $testRelease->add($this->json);
        $this->testReleaseID = $response['id'];
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Releases')->where('ReleaseID = :id')->result();
        $pdo = array('id' => $this->testReleaseID);
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->release instanceof \Apps\Releases\PatchRelease);
    }

    public function testPatchingTheReleaseWorks()
    {
        $patchJson = '{"Artist": "UnitTestPatcher", "Title": "Changed"}';
        $this->release->patch($this->testReleaseID, $patchJson);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Artist')->from('Releases')->where('ReleaseID = :id')
            ->limit(1)->result();
        $pdo = array('id' => $this->testReleaseID);
        $result = $this->database->run($sql, $pdo);
        $value = $result[0]['Artist'];

        $expected = 'UnitTestPatcher';

        $this->assertEquals($expected, $value);
    }

    public function testPatchingTheReleaseWithInvalidKey()
    {
        $patchJson = '{"ThisDoesNotExist": "UnitTestPatcher", "Title": "Changed"}';
        $response = $this->release->patch($this->testReleaseID, $patchJson);

        $this->assertEquals($response['code'], 400);
    }

    public function testPatchingTheReleaseWithInvalidReleaseID()
    {
        $patchJson = '{"Artist": "UnitTestPatcher", "Title": "Changed"}';
        $response = $this->release->patch(-3, $patchJson);

        $this->assertEquals($response['code'], 400);
    }
}
