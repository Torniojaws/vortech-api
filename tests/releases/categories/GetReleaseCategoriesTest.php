<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetReleaseCategoriesTest extends TestCase
{
    public function setUp()
    {
        $this->categories = new \Apps\Releases\Categories\GetReleaseCategories();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        // Add the test album
        $this->json = '{"title": "UnitTestAdder", "date": "2017-07-19 12:00:00", "artist": "UnitTesties",
            "credits": "This is very welcome", "people": [{"id": 1, "name": "UnitTestExampler",
            "instruments": "Synths"},{"id": 2, "name": "UnitTestBoombastic", "instruments": "Drums"}],
            "songs": [{"title": "UnitTest My Song", "duration": 305}, {"title": "UnitTest Another Piece",
            "duration": 125}, {"title": "UnitTest Helppo", "duration": 201}], "categories": [1, 2],
            "formats": [1, 3]}';

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
        $this->assertTrue($this->categories instanceof \Apps\Releases\Categories\GetReleaseCategories);
    }

    public function testGettingCategories()
    {
        $results = $this->categories->get($this->testReleaseID);
        $expected = '2';

        $this->assertEquals($expected, $results['contents'][1]['ReleaseTypeID']);
    }

    public function testGettingCategoriesWithNonExistingID()
    {
        $results = $this->categories->get(-19);
        // No matches = empty array
        $expected = array();

        $this->assertEquals($expected, $results['contents']);
    }

    /**
     * Technically it is OK to search by text, but it will never match anything
     */
    public function testGettingCategoriesWithAlphabeticID()
    {
        $results = $this->categories->get("ABC");
        $expected = array();

        $this->assertEquals($expected, $results['contents']);
    }
}
