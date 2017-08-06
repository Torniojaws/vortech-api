<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchShowTest extends TestCase
{
    public function setUp()
    {
        $this->shows = new \Apps\Shows\PatchShow();
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
        $this->assertTrue($this->shows instanceof \Apps\Shows\PatchShow);
    }

    public function testPatchingShowDetails()
    {
        $json = '{"date": "2017-08-06 00:00:00", "venue": "Somewhere Else"}';
        $response = $this->shows->patch($this->validShowID, $json);

        $sql = $this->select->select()->from('Shows')->where('ShowID = :id')->result();
        $pdo = array('id' => $this->validShowID);
        $result = $this->database->run($sql, $pdo);

        $date = $result[0]['ShowDate'];
        $venue = $result[0]['Venue'];

        $this->assertEquals(204, $response['code'], 'Wrong response code');
        $this->assertEquals('2017-08-06 00:00:00', $date, 'Date is not as expected');
        $this->assertEquals('Somewhere Else', $venue, 'Venue is wrong');
    }

    public function testPatchingShowDetailsAndOneSubdetail()
    {
        $json = '{"venue": "Magic", "setlist": ['.$this->validSongIDs[0]['SongID'].',
            {"title": "UnitTest Patchy", "duration": 440}]}';
        $response = $this->shows->patch($this->validShowID, $json);

        $sql = $this->select->select()->from('Shows')->where('ShowID = :id')->result();
        $pdo = array('id' => $this->validShowID);
        $result = $this->database->run($sql, $pdo);
        $venue = $result[0]['Venue'];

        $sql = $this->select->select()->from('ShowsSetlists')->where('ShowID = :id')->result();
        $pdo = array('id' => $this->validShowID);
        $setlist = $this->database->run($sql, $pdo);

        $this->assertEquals(204, $response['code'], 'Unexpected response');
        $this->assertEquals('Magic', $venue, 'Venue has wrong value');
        $this->assertEquals(2, count($setlist), 'Setlist has an unexpected amount of songs');
    }
}
