<?php

namespace VortechAPI\Tests\Biography;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddBiographyTest extends TestCase
{
    public function setUp()
    {
        $this->bio = new \Apps\Biography\AddBiography();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->select = new \Apps\Database\Select();
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Biography')->where('Full = :full')->result();
        $pdo = array('full' => 'Full biography');
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->bio instanceof \Apps\Biography\AddBiography);
    }

    public function testAddingBiographyCreatesANewEntry()
    {
        $json = '{"short": "Short added biography", "full": "Full added biography"}';
        $response = $this->bio->add($json);

        $bio = new \Apps\Biography\GetBiography();
        $currentBio = $bio->get()['contents'];

        $expectedShort = 'Short added biography';
        $actualShort = $currentBio['Short'];

        $expectedFull = 'Full added biography';
        $actualFull = $currentBio['Full'];

        $this->assertFalse(empty($response));
        $this->assertEquals(201, $response['code']);
        $this->assertEquals($expectedShort, $actualShort);
        $this->assertEquals($expectedFull, $actualFull);
    }

    public function testAddingBiographyWithJSONArray()
    {
        $json = '[{"short": "Short added biography", "full": "Full added biography"}]';
        $response = $this->bio->add($json);

        $bio = new \Apps\Biography\GetBiography();
        $currentBio = $bio->get()['contents'];

        $expectedShort = 'Short added biography';
        $actualShort = $currentBio['Short'];

        $expectedFull = 'Full added biography';
        $actualFull = $currentBio['Full'];

        $this->assertFalse(empty($response));
        $this->assertEquals(201, $response['code']);
        $this->assertEquals($expectedShort, $actualShort);
        $this->assertEquals($expectedFull, $actualFull);
    }

    public function testAddingBiographyWithInvalidJSONIsBadRequest()
    {
        $json = '{invalid';
        $response = $this->bio->add($json);

        $this->assertFalse(empty($response));
        $this->assertEquals(400, $response['code']);
    }
}
