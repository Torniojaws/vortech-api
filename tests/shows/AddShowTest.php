<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddShowTest extends TestCase
{
    public function setUp()
    {
        $this->shows = new \Apps\Shows\AddShow();
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
        $select = new \Apps\Database\Select();
        $sql = $select->select('PersonID')->from('People')->where('Name LIKE :name')->result();
        $pdo = array('name' => 'UnitTest%');
        $personIDs = $this->database->run($sql, $pdo);

        // Get four songIDs
        $sql = $select->select('SongID')->from('Songs')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $validSongIDs = $this->database->run($sql, $pdo);

        $this->json = '{"date": "2015-01-02 00:00:00", "countryCode": "FI", "country": "UnitTest",
            "city": "Tornio", "venue": "Jesperi", "setlist": ['.$validSongIDs[2]['SongID'].',
            '.$validSongIDs[0]['SongID'].', '.$validSongIDs[1]['SongID'].', {"title": "UnitTest New Song",
                "duration": 95}, '.$validSongIDs[3]['SongID'].'], "otherBands": [
                {"name": "The Band", "website": "http://theband.com"},
                {"name": "Another One", "website": "http://www.another.fi"},
                {"name": "One More Band", "website": "http://www.one.mr"}
            ], "performers": [{"personID": '.$personIDs[0]['PersonID'].', "instruments": "Guitar"},
            {"personID": '.$personIDs[1]['PersonID'].', "instruments": "Vocals"},
            {"personID": "UnitTest New", "instruments": "Bongo"}]}';
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
        $this->assertTrue($this->shows instanceof \Apps\Shows\AddShow);
    }

    public function testAddingShowWithValidData()
    {
        $response = $this->shows->add($this->json);

        // Get show
        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select()->from('Shows')->where('Country = :country')->result();
        $pdo = array('country' => 'UnitTest');
        $show = $this->database->run($sql, $pdo);

        // Get show setlist
        $sql = $sqlBuilder->select()->from('ShowsSetlists')->where('ShowID = :id')
            ->order('ShowID DESC')->result();
        $pdo = array('id' => $response['id']);
        $setlist = $this->database->run($sql, $pdo);

        // Get show's other bands
        $sql = $sqlBuilder->select()->from('ShowsOtherBands')->where('ShowID = :id')
            ->order('ShowID DESC')->result();
        $pdo = array('id' => $response['id']);
        $bands = $this->database->run($sql, $pdo);

        // Get show's performers
        $sql = $sqlBuilder->select()->from('ShowsPeople')
            ->joins('JOIN People ON People.PersonID = ShowsPeople.PersonID')
            ->where('ShowID = :id')->result();
        $pdo = array('id' => $response['id']);
        $performers = $this->database->run($sql, $pdo);

        $expected = 'Location: http://www.vortechmusic.com/api/1.0/shows/'.$response['id'];
        $this->assertEquals(201, $response['code'], 'Response code was not as expected');
        $this->assertEquals($expected, $response['contents'], 'Response contents unexpected');
        $this->assertEquals('Tornio', $show[0]['City'], 'City is not as expected');
        $this->assertEquals(5, count($setlist), 'Setlist array was too small');
        $this->assertEquals(3, count($bands), 'Too few bands');
        $this->assertEquals('The Band', $bands[0]['BandName'], 'Other band name is unexpected');
        $this->assertEquals(3, count($performers), 'People count does not match');
        $this->assertEquals('UnitTest New', $performers[2]['Name'], 'New name was not added');
    }
}
