<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchSongsSongsTest extends TestCase
{
    public function setUp()
    {
        $this->songs = new \Apps\Songs\PatchSongs();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();
        $this->select = new \Apps\Database\Select();

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
        $this->assertTrue($this->songs instanceof \Apps\Songs\PatchSongs, 'Class instance is not as expected');
    }

    public function testPatchingTitleWithValidData()
    {
        $json = '{"title": "UnitTest Patched"}';
        $response = $this->songs->patch($this->validSongID, $json);
        $expected = 'UnitTest Patched';

        $sql = $this->select->select('Title')->from('Songs')->where('SongID = :id')->result();
        $pdo = array('id' => $this->validSongID);
        $result = $this->database->run($sql, $pdo);
        $actual = $result[0]['Title'];

        $this->assertEquals($expected, $actual, 'Song title does not match expected');
        $this->assertEquals(204, $response['code']);
        $this->assertTrue(empty($response['contents']));
    }

    public function testPatchingDurationWithValidData()
    {
        $json = '{"duration": 223}';
        $response = $this->songs->patch($this->validSongID, $json);
        $expected = 223;

        $sql = $this->select->select('Duration')->from('Songs')->where('SongID = :id')->result();
        $pdo = array('id' => $this->validSongID);
        $result = $this->database->run($sql, $pdo);
        $actual = intval($result[0]['Duration']);

        $this->assertEquals($expected, $actual, 'Song duration does not match expected');
        $this->assertEquals(204, $response['code']);
        $this->assertTrue(empty($response['contents']));
    }

    public function testPatchingTitleWithInvalidData()
    {
        $json = '{"title';
        $response = $this->songs->patch($this->validSongID, $json);

        $sql = $this->select->select('Title')->from('Songs')->where('SongID = :id')->result();
        $pdo = array('id' => $this->validSongID);
        $result = $this->database->run($sql, $pdo);
        $actual = $result[0]['Title'];

        // This is the original value. Should be unchanged with invalid patch.
        $expected = 'UnitTesting';

        $this->assertEquals($expected, $actual, 'Song title changed, but it should not have');
        $this->assertEquals(400, $response['code']);
        $this->assertFalse(empty($response['contents']));
    }
}
