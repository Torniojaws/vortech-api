<?php

namespace VortechAPI\Tests\Shows;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetShowsTest extends TestCase
{
    public function setUp()
    {
        $this->shows = new \Apps\Shows\GetShows();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        // Add songs
        $songs = new \Apps\Songs\AddSongs();
        $testSongs = array();
        $testSongs[] = array('title' => 'UnitTest1', 'duration' => 99);
        $testSongs[] = array('title' => 'UnitTest2', 'duration' => 95);
        $testSongs[] = array('title' => 'UnitTest3', 'duration' => 102);
        $testSongs[] = array('title' => 'UnitTest4', 'duration' => 88);
        $songs->add($testSongs);

        // Get four songIDs
        $select = new \Apps\Database\Select();
        $sql = $select->select('SongID')->from('Songs')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $validSongIDs = $this->database->run($sql, $pdo);

        // We'll also need to add the two People that we refer to
        $people = new \Apps\People\AddPeople();
        $json = '[{"name": "UnitTest Guitar"}, {"name": "UnitTest Bass"}]';
        $people->add($json);

        // Get the people's IDs
        $select = new \Apps\Database\Select();
        $sql = $select->select('PersonID')->from('People')->where('Name LIKE :name')->result();
        $pdo = array('name' => 'UnitTest%');
        $personIDs = $this->database->run($sql, $pdo);

        // Add two test shows
        $shows = new \Apps\Shows\AddShow();

        // Show 1
        $json1 = '{"date": "2015-01-02 00:00:00", "countryCode": "FI", "country": "UnitTest",
            "city": "Tornio", "venue": "Jesperi", "setlist": ['.$validSongIDs[2]['SongID'].',
            '.$validSongIDs[0]['SongID'].', '.$validSongIDs[1]['SongID'].', {"title": "UnitTest New Song",
                "duration": 95}, '.$validSongIDs[3]['SongID'].'], "otherBands": [
                {"name": "The Band", "website": "http://theband.com"},
                {"name": "Another One", "website": "http://www.another.fi"},
                {"name": "One More Band", "website": "http://www.one.mr"}
            ], "performers": [{"personID": '.$personIDs[0]['PersonID'].', "instruments": "Guitar"},
            {"personID": '.$personIDs[1]['PersonID'].', "instruments": "Vocals"},
            {"personID": "UnitTest New", "instruments": "Bongo"}]}';

        // Show 2
        $json2 = '{"date": "2016-02-03 00:00:00", "countryCode": "SE", "country": "UnitTest",
            "city": "Stockholm", "venue": "Debaser", "setlist": ['.$validSongIDs[0]['SongID'].',
            '.$validSongIDs[2]['SongID'].', '.$validSongIDs[1]['SongID'].',
            '.$validSongIDs[3]['SongID'].'], "otherBands": [
                {"name": "My First Band", "website": "http://mfb.com"},
                {"name": "Somewhere", "website": "http://www.somwhere.se"},
                {"name": "Tester", "website": "http://www.tes.tr"}
            ], "performers": [{"personID": '.$personIDs[0]['PersonID'].', "instruments": "Guitar"},
            {"personID": '.$personIDs[1]['PersonID'].', "instruments": "Vocals"},
            {"personID": "UnitTest New", "instruments": "Bongo"}]}';
        $this->testShowID1 = $shows->add($json1)['id'];
        $this->testShowID2 = $shows->add($json2)['id'];
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
        $this->assertTrue($this->shows instanceof \Apps\Shows\GetShows);
    }

    public function testGettingOneShow()
    {
        $result = $this->shows->get($this->testShowID2);

        $this->assertFalse(empty($result), 'Result should not be empty');
        $this->assertTrue(array_key_exists('setlist', $result), 'Setlist is missing from response');
        $this->assertTrue(array_key_exists('otherBands', $result), 'Other bands are missing from response');
        $this->assertEquals('SE', $result['countryCode'], 'Country code is wrong');
        $this->assertEquals(3, count($result['otherBands']), 'Amount of bands is wrong');
        $this->assertEquals('UnitTest3', $result['setlist'][1]['songTitle'], 'Song title is wrong in index 1');
        $this->assertEquals('Tester', $result['otherBands'][2]['name'], 'Other bands is missing band');
        $this->assertEquals('UnitTest New', $result['performers'][2]['name'], 'Player name is unexpected');
    }

    public function testGettingAllShows()
    {
        $shows = $this->shows->get();

        $this->assertEquals(2, count($shows), 'Show count does not match');
        $this->assertEquals('SE', $shows[1]['countryCode'], 'Country code does not match');
        $this->assertTrue(array_key_exists('setlist', $shows[0]));
        $this->assertTrue(array_key_exists('otherBands', $shows[0]));
    }
}
