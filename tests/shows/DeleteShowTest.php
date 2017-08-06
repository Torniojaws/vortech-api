<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DeleteShowTest extends TestCase
{
    public function setUp()
    {
        $this->deleteShow = new \Apps\Shows\DeleteShow();

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

        // Create a test show
        $response = $this->shows->add($this->json);
        $this->validShowID = $response['id'];
    }

    public function tearDown()
    {
        // Just in case :)
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
        $this->assertTrue($this->deleteShow instanceof \Apps\Shows\DeleteShow);
    }

    public function testDeleteWorksWithValidID()
    {
        $this->deleteShow->delete($this->validShowID);

        $check = new \Apps\Utils\DatabaseCheck();
        $showsExists = $check->existsInTable('Shows', 'ShowID', $this->validShowID);
        $setlistExists = $check->existsInTable('ShowsSetlists', 'ShowID', $this->validShowID);
        $showsPeopleExist = $check->existsInTable('ShowsPeople', 'ShowID', $this->validShowID);
        $showsBandsExist = $check->existsInTable('ShowsOtherBands', 'ShowID', $this->validShowID);

        $this->assertFalse($showsExists, 'ShowID exist in Shows table - should not');
        $this->assertFalse($setlistExists, 'ShowID exist in ShowsSetlists table - should not');
        $this->assertFalse($showsPeopleExist, 'ShowID exist in ShowsPeople table - should not');
        $this->assertFalse($showsBandsExist, 'ShowID exist in ShowsOtherBands table - should not');
    }

    public function testDeleteReturnsExpectedResponseWithInvalidID()
    {
        $response = $this->deleteShow->delete(-25);

        $this->assertEquals($response['code'], 400);
    }
}
