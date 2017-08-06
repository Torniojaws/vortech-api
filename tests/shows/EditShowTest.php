<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class EditShowTest extends TestCase
{
    public function setUp()
    {
        $this->shows = new \Apps\Shows\EditShow();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        // The songs referred to in the JSON below must exist in Songs, so let's add them
        $songs = new \Apps\Songs\AddSongs();
        $testSongs = array();
        $testSongs[] = array('title' => 'UnitTest1', 'duration' => 99);
        $testSongs[] = array('title' => 'UnitTest2', 'duration' => 95);
        $testSongs[] = array('title' => 'UnitTest3', 'duration' => 102);
        $testSongs[] = array('title' => 'UnitTest4', 'duration' => 88);
        $songs->add($testSongs);

        // We'll also need to add the two People that we refer to
        $people = new \Apps\People\AddPeople();
        $json = '[{"name": "UnitTest Guitar"}, {"name": "UnitTest Bass"}]';
        $people->add($json);

        // Get the people's IDs
        $this->select = new \Apps\Database\Select();
        $sql = $this->select->select('PersonID')->from('People')->where('Name LIKE :name')->result();
        $pdo = array('name' => 'UnitTest%');
        $this->personIDs = $this->database->run($sql, $pdo);

        // Get four songIDs
        $sql = $this->select->select('SongID')->from('Songs')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->validSongIDs = $this->database->run($sql, $pdo);

        $json = '{"date": "2015-01-02 00:00:00", "countryCode": "FI", "country": "UnitTest",
            "city": "Tornio", "venue": "Jesperi", "setlist": ['.$this->validSongIDs[2]['SongID'].',
            '.$this->validSongIDs[0]['SongID'].', '.$this->validSongIDs[1]['SongID'].',
            {"title": "UnitTest New Song", "duration": 95}, '.$this->validSongIDs[3]['SongID'].'],
            "otherBands": [
                {"name": "The Band", "website": "http://theband.com"},
                {"name": "Another One", "website": "http://www.another.fi"},
                {"name": "One More Band", "website": "http://www.one.mr"}
            ], "performers": [{"personID": '.$this->personIDs[0]['PersonID'].', "instruments": "Guitar"},
            {"personID": '.$this->personIDs[1]['PersonID'].', "instruments": "Vocals"},
            {"personID": "UnitTest New", "instruments": "Bongo"}]}';
        $shows = new \Apps\Shows\AddShow();
        $response = $shows->add($json);
        $this->validShowID = $response['id'];
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Shows')->where('Country = :country')->result();
        $pdo = array('country' => 'UnitTest');
        $this->database->run($sql, $pdo);

        $sql = $sqlBuilder->delete()->from('Songs')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $sql = $sqlBuilder->delete()->from('People')->where('Name LIKE :name')->result();
        $pdo = array('name' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->shows instanceof \Apps\Shows\EditShow);
    }

    public function testEditingAShowWithValidData()
    {
        $json = '{"date": "2015-01-03 00:00:00", "countryCode": "FI", "country": "UnitTest2",
            "city": "Tornio", "venue": "Jesperi2", "setlist": ['.$this->validSongIDs[0]['SongID'].',
            '.$this->validSongIDs[2]['SongID'].', '.$this->validSongIDs[1]['SongID'].',
            {"title": "UnitTest New Song2", "duration": 95}, '.$this->validSongIDs[3]['SongID'].'],
            "otherBands": [
                {"name": "The Band", "website": "http://theband.com"},
                {"name": "Another One2", "website": "http://www.another.fi"},
                {"name": "One More Band", "website": "http://www.one.mr"}
            ], "performers": [{"personID": '.$this->personIDs[0]['PersonID'].', "instruments": "Guitar"},
            {"personID": '.$this->personIDs[1]['PersonID'].', "instruments": "Vocals"},
            {"personID": "UnitTest New Guy", "instruments": "Drums"}]}';
        $response = $this->shows->edit($this->validShowID, $json);

        // Get the data from the DB after updating
        $sql = $this->select->select()->from('Shows')->where('ShowID = :id')->result();
        $pdo = array('id' => $this->validShowID);
        $show = $this->database->run($sql, $pdo);

        // For the songs, we have a special situation in that the original JSON in setUp added
        // a new song that did not exist, and now in this edit case that new song is not in the
        // setlist anymore. However, there is yet another new song that will be added. Both songs
        // will remain in the database, but only the second new song will be in this show's setlist
        $sql = $this->select->select()->from('ShowsSetlists')->where('ShowID = :id')->result();
        $pdo = array('id' => $this->validShowID);
        $setlist = $this->database->run($sql, $pdo);

        $sql = $this->select->select()->from('Songs')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest New%'); // Should only get 2 results
        $newSongs = $this->database->run($sql, $pdo);

        $sql = $this->select->select()->from('ShowsOtherBands')->where('ShowID = :id')->result();
        $pdo = array('id' => $this->validShowID);
        $bands = $this->database->run($sql, $pdo);

        $sql = $this->select->select()->from('ShowsPeople')->where('ShowID = :id')->result();
        $pdo = array('id' => $this->validShowID);
        $peopleInShow = $this->database->run($sql, $pdo);

        $sql = $this->select->select()->from('People')->where('Name LIKE :name')->result();
        $pdo = array('name' => 'UnitTest New Guy');
        $person = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'Received an empty response!');
        $this->assertEquals(200, $response['code']);
        $this->assertFalse(empty($show), 'No show data was found');
        $this->assertEquals('UnitTest2', $show[0]['Country']);
        $this->assertEquals('Jesperi2', $show[0]['Venue']);
        $this->assertEquals(5, count($setlist));
        $this->assertEquals(2, count($newSongs), 'Did not find expected amount of new songs');
        $this->assertEquals(3, count($bands));
        $this->assertEquals('Another One2', $bands[1]['BandName']);
        $this->assertEquals(3, count($peopleInShow), 'The amount of people is wrong');
        $this->assertEquals('UnitTest New Guy', $person[0]['Name'], 'A new person was not added successfully');
    }

    public function testEditingAShowWithInvalidJSON()
    {
        $json = '{hepp';
        $response = $this->shows->edit($this->validShowID, $json);

        $this->assertEquals(400, $response['code']);
    }
}
