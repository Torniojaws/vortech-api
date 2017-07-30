<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddSongsTest extends TestCase
{
    public function setUp()
    {
        $this->songs = new \Apps\Songs\AddSongs();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();
    }

    public function tearDown()
    {
        $delete = new \Apps\Database\Delete();
        $sql = $delete->delete()->from('Songs')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->songs instanceof \Apps\Songs\AddSongs);
    }

    public function testAddOneNewSongUsingValidJSONObject()
    {
        // When accessed via API, a JSON is posted. The API handler does json_decode($json, true)
        // before the new song(s) is passed to the add() method.
        $json = '{"title": "UnitTest Add", "duration": 100}';
        $array = json_decode($json, true);

        $response = $this->songs->add($array);

        $this->assertEquals(201, $response['code']);
    }

    public function testIsDataSimpleArrayWhenASimpleArrayIsPassed()
    {
        $json = '{"title": "UnitTest Add", "duration": 100}';
        $array = json_decode($json, true);

        $this->assertTrue($this->songs->isSimpleArray($array));
    }

    public function testIsDataSimpleArrayWhenAnArrayOfObjectsIsPassed()
    {
        $json = '[{"title": "UnitTest 1", "duration": 234}, {"title": "UnitTest 2", "duration": 321},
            {"title": "UnitTest 3", "duration": 440}]';
        $array = json_decode($json, true);

        $this->assertFalse($this->songs->isSimpleArray($array));
    }

    public function testAddMultipleNewSongsUsingValidJSON()
    {
        $json = '[{"title": "UnitTest 1", "duration": 234}, {"title": "UnitTest 2", "duration": 321},
            {"title": "UnitTest 3", "duration": 440}]';
        $array = json_decode($json, true);

        $response = $this->songs->add($array);

        $this->assertEquals(201, $response['code']);
    }

    public function testAddingInvalidPropertiesWithOneObject()
    {
        $json = '{"title": "UnitTest Invalid", "doesnot": "exist"}';
        $array = json_decode($json, true);

        $response = $this->songs->add($array);

        $this->assertEquals(400, $response['code']);
        $this->assertEquals('Invalid data', $response['contents']);
    }

    public function testAddingInvalidPropertiesInOneOfMultipleObjects()
    {
        $json = '[{"title": "UnitTest 1", "duration": 234}, {"title": "UnitTest 2", "doesnot": 321},
            {"title": "UnitTest 3", "duration": 440}]';
        $array = json_decode($json, true);

        $response = $this->songs->add($array);

        // The invalid item will be ignored, but everything else will be inserted normally
        $this->assertEquals(201, $response['code']);
    }

    public function testAddingInvalidPropertiesInAllOfMultipleObjects()
    {
        $json = '[{"doesnot": "UnitTest 1", "duration": 234}, {"title": "UnitTest 2", "nope": 321},
            {"wrong": "UnitTest 3", "duration": 440}]';
        $array = json_decode($json, true);

        $response = $this->songs->add($array);

        // The invalid item will be ignored, but everything else will be inserted normally
        $this->assertEquals(400, $response['code']);
        $this->assertEquals('Invalid data', $response['contents']);
    }
}
