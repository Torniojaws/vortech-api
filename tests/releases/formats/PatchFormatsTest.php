<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchFormatsTest extends TestCase
{
    public function setUp()
    {
        $this->formats = new \Apps\Releases\Formats\PatchFormats();
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
        $this->assertTrue($this->formats instanceof \Apps\Releases\Formats\PatchFormats);
    }

    public function testPatchingFormats()
    {
        $patch = '{"formats": [2, 4]}';
        $this->formats->patch($this->testReleaseID, $patch);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('ReleaseFormats.FormatID')->from('ReleaseFormats')
            ->joins('JOIN Formats ON Formats.FormatID = ReleaseFormats.FormatID')
            ->where('ReleaseFormats.ReleaseID = :id')->result();
        $pdo = array('id' => $this->testReleaseID);
        $result = $this->database->run($sql, $pdo);

        // The results come as a string array from the PDO
        $expected = array('1', '2', '3', '4');

        foreach ($result as $formatObj) {
            $test[] = $formatObj['FormatID'];
        }

        sort($test);

        $this->assertEquals($expected, $test);
    }

    public function testPatchingFormatsWithInvalidData()
    {
        $patch = '[{"id": 2, "name": "UnitTestExampler", "instruments": "Drums and Bass"},
            {"id": 3, "name": "Test", "instruments": "Drums and Bass"}]';
        $response = $this->formats->patch($this->testReleaseID, $patch);
        $expected = 400;

        $this->assertEquals($expected, $response['code']);
    }
}
