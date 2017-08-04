<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class EditSongsSongsTest extends TestCase
{
    public function setUp()
    {
        $this->songs = new \Apps\Songs\EditSongs();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        // Add some songs for testing
        $insert = new \Apps\Database\Insert();

        $sql = $insert->insert()->into('Songs(Title, Duration)')->values(':title, :duration')->result();
        $pdo = array('title' => "UnitTesting", 'duration' => 123);
        $this->database->run($sql, $pdo);
        $this->validSongID = $this->database->getInsertId();
        $this->validSongIDArray[] = $this->validSongID;

        $sql = $insert->insert()->into('Songs(Title, Duration)')->values(':title, :duration')->result();
        $pdo = array('title' => "UnitTest Too", 'duration' => 222);
        $this->database->run($sql, $pdo);
        $secondValid = $this->database->getInsertId();
        $this->validSongIDArray[] = $secondValid;
    }

    public function tearDown()
    {
        // Remove the songs added for the test cases
        $delete = new \Apps\Database\Delete();
        $sql = $delete->delete()->from('Songs')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->songs instanceof \Apps\Songs\EditSongs, 'Class instance is not as expected');
    }

    public function testEditingASongIDThatExists()
    {
        $json = '{"title": "UnitTest Edited", "duration": 99}';
        $response = $this->songs->edit($this->validSongID, $json);

        // Check from DB, too
        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select()->from('Songs')->where('SongID = :id')->limit(1)->result();
        $pdo = array('id' => $this->validSongID);
        $result = $this->database->run($sql, $pdo);
        $songTitle = $result[0]['Title'];
        $songDuration = intval($result[0]['Duration']);

        $expectedTitle = 'UnitTest Edited';
        $expectedDuration = 99;

        $this->assertTrue($response['code'] == 200, 'Response code is not expected 200');
        $this->assertEquals($expectedTitle, $songTitle, 'Song title is not as expected');
        $this->assertEquals($expectedDuration, $songDuration, 'Song duration is not as expected');
    }

    public function testEditingASongIDThatExistsWithInvalidJSON()
    {
        $json = '{hello';
        $response = $this->songs->edit($this->validSongID, $json);

        $this->assertTrue($response['code'] == 400, 'Response code is not expected 400');
    }

    public function testEditingASongIDThatDoesNotExist()
    {
        $json = '{"title": "Test", "duration": 99}';
        $response = $this->songs->edit(-23, $json);

        $this->assertFalse(empty($response), 'Response is empty');
        $this->assertTrue(is_array($response), 'Response is not an array');
        $this->assertTrue($response['code'] == 400, 'Response code is not expected 400');
    }
}
