<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class EditReleaseTest extends TestCase
{
    public function setUp()
    {
        $this->release = new \Apps\Releases\EditRelease();

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
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->release instanceof \Apps\Releases\EditRelease);
    }

    public function testReleaseIsEdited()
    {
        $edited = '{"title": "UnitTestAdder2", "date": "2017-07-19 12:00:00", "artist": "UnitTesties",
            "credits": "This is very welcome too", "people": [{"id": 1, "name": "UnitTestExampler",
            "instruments": "Synths"},{"id": 2, "name": "UnitTestBoombastic", "instruments": "Drums"}],
            "songs": [{"title": "UnitTest My Song", "duration": 305}, {"title": "UnitTest Another Piece",
            "duration": 125}, {"title": "UnitTest Helppo", "duration": 201}], "categories": [1, 2],
            "formats": [1, 3]}';

        $this->release->edit($this->testReleaseID, $edited);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Title')->from('Releases')->where('ReleaseID = :id')->limit(1)->result();
        $pdo = array('id' => $this->testReleaseID);
        $result = $this->database->run($sql, $pdo);

        $expected = 'UnitTestAdder2';
        $actual = $result[0]['Title'];

        $this->assertEquals($expected, $actual);
    }

    /**
     * By executive decision, you are only allowed to edit the instruments for the person per album.
     * You cannot update their name as that would become messy to manage, and detecting the name change
     * would be hard. If a new non-existing person appears, it will be skipped. There will be a separate
     * endpoint for adding new people after-the-fact, and there you can assign them to a given existing
     * album.
     */
    public function testReleasePeopleAreEdited()
    {
        $edited = '{"title": "UnitTestAdder", "date": "2017-07-19 12:00:00", "artist": "UnitTesties",
            "credits": "This is very welcome too", "people": [{"id": 1, "name": "UnitTestExampler",
            "instruments": "Synths, Vocals"},{"id": 2, "name": "UnitTestBoombastic", "instruments": "Drums, Bass"}],
            "songs": [{"title": "UnitTest My Song", "duration": 305}, {"title": "UnitTest Another Piece",
            "duration": 125}, {"title": "UnitTest Helppo", "duration": 201}], "categories": [1, 2],
            "formats": [1, 3]}';

        $this->release->edit($this->testReleaseID, $edited);

        $name = 'UnitTestBoombastic';
        $personID = $this->release->getPersonID($name);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Instruments')->from('ReleasePeople')
            ->where('ReleaseID = :id AND PersonID = :pid')->limit(1)->result();
        $pdo = array('id' => $this->testReleaseID, 'pid' => $personID);
        $result = $this->database->run($sql, $pdo);

        $expected = 'Drums, Bass';
        $actual = $result[0]['Instruments'];

        $this->assertEquals($expected, $actual);
    }

    public function testPersonID()
    {
        $name = 'UnitTestBoombastic';
        $pid = $this->release->getPersonID($name);

        $this->assertTrue(is_numeric($pid) && $pid > 0);
    }

    public function testEditPerson()
    {
        $edited = '{"title": "UnitTestAdder23", "date": "2017-07-19 12:00:00", "artist": "UnitTesties",
            "credits": "This is very welcome too", "people": [{"id": 1, "name": "UnitTestExampler2",
            "instruments": "Synths, Vocals"},{"id": 2, "name": "UnitTestBoombastic", "instruments": "Drums, Bass"}],
            "songs": [{"title": "UnitTest My Song", "duration": 305}, {"title": "UnitTest Another Piece",
            "duration": 125}, {"title": "UnitTest Helppo", "duration": 201}], "categories": [1, 2],
            "formats": [1, 3]}';
        $json = json_decode($edited, true);
        $people = $json['people'];
        $this->release->editPerson($people[1], $this->testReleaseID);

        $name = 'UnitTestBoombastic';
        $personID = $this->release->getPersonID($name);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Instruments')->from('ReleasePeople')
            ->where('ReleaseID = :id AND PersonID = :pid')->limit(1)->result();
        $pdo = array('id' => $this->testReleaseID, 'pid' => $personID);
        $result = $this->database->run($sql, $pdo);

        $expected = 'Drums, Bass';

        $this->assertEquals($expected, $result[0]['Instruments']);
    }
}
