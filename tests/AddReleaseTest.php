<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddReleaseTest extends TestCase
{
    public function setUp()
    {
        $this->release = new \Apps\Releases\AddRelease();

        $this->add = new \Apps\Database\Insert();
        $this->get = new \Apps\Database\Select();
        $this->remove = new \Apps\Database\Delete();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->json = '{"title": "Adder", "date": "2017-07-19 12:00:00", "artist": "Testies",
            "credits": "This is very welcome", "people": [{"id": 1, "name": "Exampler",
            "instruments": "Synths"},{"id": 2, "name": "Boombastic", "instruments": "Drums"}],
            "songs": [{"title": "My Song", "duration": 305}, {"title": "Another Piece",
            "duration": 125}, {"title": "Helppo", "duration": 201}], "categories": [1, 2],
            "formats": [1, 3]}';
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->release instanceof \Apps\Releases\AddRelease);
    }

    public function testAddingRelease()
    {
        $this->release->add($this->json);

        $sql = $this->get->select()->from('Releases')->where('Title = :title')->limit(1)->result();
        $pdo = array('title' => 'Adder');
        $result = $this->database->run($sql, $pdo);
        $expected = 'This is very welcome';

        $this->assertEquals($expected, $result[0]['Credits']);
    }

    /**
     * When a new release is added, it also creates (if needed) some related sets of data into
     * other tables. This tests the People table, which keeps track who played what instruments
     * on various albums.
     */
    public function testAddingReleaseCreatesRelatedPeopleEntries()
    {
        $this->release->add($this->json);

        $sql = $this->get->select()->from('People')
            ->joins('JOIN ReleasePeople ON ReleasePeople.PersonID = People.PersonID')
            ->where('Name = :name')->limit(1)->result();
        $pdo = array('title' => 'Exampler');
        $result = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($result));
    }

    public function testAddingReleaseWithInvalidJSON()
    {
        $response = $this->release->add('{"notvalid');
        $expected = 400;

        $this->assertEquals($expected, $response['code']);
    }

    public function tearDown()
    {
        /*
        $sql = $this->remove->delete()->from('Releases')->where('Title = :title')->result();
        $pdo = array('title' => 'Adder');
        $this->database->run($sql, $pdo);

        $sql = $this->remove->delete()->from('People')->where('Name = :name')->result();
        $pdo = array('name' => 'Exampler');
        $this->database->run($sql, $pdo);
        */
    }
}
