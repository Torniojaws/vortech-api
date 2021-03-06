<?php

namespace VortechAPI\Tests\Releases;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetPeopleTest extends TestCase
{
    public function setUp()
    {
        $this->people = new \Apps\Releases\People\GetPeople();

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
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Releases')->where('ReleaseID = :id')->result();
        $pdo = array('id' => $this->testReleaseID);
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->people instanceof \Apps\Releases\People\GetPeople);
    }

    public function testGettingPeople()
    {
        $results = $this->people->get($this->testReleaseID);

        // The index changes between runs, since SQL results are not always in the same order
        $nameExists = false;
        $expected = 'UnitTestExampler';
        if ($results['contents'][0]['Name'] == $expected
            || $results['contents'][1]['Name'] == $expected) {
            $nameExists = true;
        }

        $this->assertEquals(2, count($results));
        $this->assertTrue($nameExists, 'Expected name was not found in results!');
    }

    public function testGettingPeopleWithNonExistingID()
    {
        $results = $this->people->get(-19);
        // No matches = empty array
        $expected = array();

        $this->assertEquals($expected, $results['contents']);
    }
}
