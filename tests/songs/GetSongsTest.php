<?php

namespace VortechAPI\Tests\Songs;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetSongsSongsTest extends TestCase
{
    public function setUp()
    {
        $this->songs = new \Apps\Songs\GetSongs();

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
        $this->assertTrue($this->songs instanceof \Apps\Songs\GetSongs);
    }

    public function testGettingSongsWithoutParamsGetsAllSongs()
    {
        $result = $this->songs->get();

        // The count is more than 1, because in the test setup we insert exactly 2 items,
        // so we should get at least two results.
        $this->assertTrue(count($result) > 1);
        $this->assertTrue(array_key_exists('Title', $result[0]));
    }

    public function testGettingSongsWithASpecificIDThatExists()
    {
        $result = $this->songs->get($this->validSongID);

        $this->assertTrue(count($result) == 1);
        $this->assertEquals($this->validSongID, $result[0]['SongID']);
    }
}
