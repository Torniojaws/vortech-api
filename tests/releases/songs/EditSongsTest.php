<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class EditSongsTest extends TestCase
{
    public function setUp()
    {
        $this->songs = new \Apps\Releases\Songs\EditSongs();
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

        // Get valid SongIDs
        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Songs.SongID')->from('Songs')
            ->joins('JOIN ReleaseSongs ON ReleaseSongs.SongID = Songs.SongID')
            ->where('ReleaseSongs.ReleaseID = :rid')->result();
        $pdo = array('rid' => $this->testReleaseID);
        $result = $this->database->run($sql, $pdo);

        // Flatten the array of arrays
        $arrUtil = new \Apps\Utils\Arrays();
        $this->validNewSongIDs = $arrUtil->flattenArray($result, 'SongID');
        $this->validSongID = $this->validNewSongIDs[0];
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Releases')->where('ReleaseID = :id')->result();
        $pdo = array('id' => $this->testReleaseID);
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->songs instanceof \Apps\Releases\Songs\EditSongs);
    }

    public function testEditingSongs()
    {
        $json = json_encode($this->validNewSongIDs);
        $this->songs->edit($this->testReleaseID, $json);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('SongID')->from('ReleaseSongs')->where('ReleaseID = :id')->result();
        $pdo = array('id' => $this->testReleaseID);
        $result = $this->database->run($sql, $pdo);

        $expected = $this->validNewSongIDs[0];
        $this->assertEquals($expected, $result[0]['SongID']);
    }

    public function testEditingWithOneSongIDThatDoesNotExist()
    {
        $edit = $this->validNewSongIDs;
        $edit[] = -19; // This does not exist, obviously
        $json = json_encode($edit);
        $response = $this->songs->edit($this->testReleaseID, $json);
        $expected = 400;

        $this->assertEquals($expected, $response['code']);
    }

    public function testAllSongsExistWithIDsThatExist()
    {
        $songIDs = $this->validNewSongIDs;

        $this->assertTrue($this->songs->allSongsExist($songIDs));
    }

    public function testAllSongsExistWithIDsThatExistAndOneThatDoesNot()
    {
        $songIDs = $this->validNewSongIDs;
        $songIDs[] = -21;
        $this->assertFalse($this->songs->allSongsExist($songIDs));
    }
}
