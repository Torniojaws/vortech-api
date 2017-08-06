<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchPeopleTest extends TestCase
{
    public function setUp()
    {
        $this->people = new \Apps\Releases\People\PatchPeople();
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
        $this->assertTrue($this->people instanceof \Apps\Releases\People\PatchPeople);
    }

    public function testPatchingPerson()
    {
        $patch = '{"id": 2, "name": "UnitTestExampler", "instruments": "Drums and Bass"}';
        $this->people->patch($this->testReleaseID, $patch);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Instruments')->from('ReleasePeople')
            ->joins('JOIN People ON People.PersonID = ReleasePeople.PersonID')
            ->where('ReleaseID = :id')->result();
        $pdo = array('id' => $this->testReleaseID);
        $result = $this->database->run($sql, $pdo);

        // Sort results alphabetically by "Instruments"
        usort($result, function ($person1, $person2) {
            return $person1['Instruments'] <=> $person2['Instruments'];
        });
        $expected = 'Drums and Bass';

        $this->assertEquals($expected, $result[1]['Instruments']);
    }

    public function testGettingPersonID()
    {
        $name = 'UnitTestExampler';
        $pid = $this->people->getPersonID($name);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('People.PersonID')->from('People')
            ->joins('JOIN ReleasePeople ON ReleasePeople.PersonID = People.PersonID')
            ->where('ReleasePeople.ReleaseID = :id AND People.Name = :name')->limit(1)->result();
        $pdo = array('id' => $this->testReleaseID, 'name' => $name);
        $result = $this->database->run($sql, $pdo);
        $expected = intval($result[0]['PersonID']);

        $this->assertEquals($expected, $pid);
    }

    public function testPatchingPersonWithInvalidData()
    {
        $patch = '[{"id": 2, "name": "UnitTestExampler", "instruments": "Drums and Bass"},
            {"id": 3, "name": "Test", "instruments": "Drums and Bass"}]';
        $response = $this->people->patch($this->testReleaseID, $patch);
        $expected = 400;

        $this->assertEquals($expected, $response['code']);
    }
}
