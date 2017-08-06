<?php

namespace VortechAPI\Tests\People;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetPeopleTest extends TestCase
{
    public function setUp()
    {
        $this->people = new \Apps\People\GetPeople();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $json = '[{"name": "UnitTest Person1"}, {"name": "UnitTest Person2"}]';
        $add = new \Apps\People\AddPeople();
        $add->add($json);

        // Get their IDs
        $this->sqlBuilder = new \Apps\Database\Select();
        $sql = $this->sqlBuilder->select('PersonID')->from('People')->where('Name LIKE :name')->result();
        $pdo = array('name' => 'UnitTest%');
        $result = $this->database->run($sql, $pdo);

        $this->arrays = new \Apps\Utils\Arrays();
        $this->validPeopleIDs = $this->arrays->flattenArray($result, 'PersonID');
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('People')->where('Name LIKE :name')->result();
        $pdo = array('name' => 'UnitTest%');
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->people instanceof \Apps\People\GetPeople);
    }

    public function testGettingAllPeople()
    {
        $response = $this->people->get();

        $names = $this->arrays->flattenArray($response['contents'], 'Name');

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(200, $response['code'], 'Response code was unexpected');
        $this->assertEquals(2, count($response['contents']), 'Result count is unexpected');
        $this->assertTrue(in_array('UnitTest Person1', $names));
    }

    public function testGettingSpecificPerson()
    {
        $personID = intval($this->validPeopleIDs[0]);
        $response = $this->people->get($personID);

        $sql = $this->sqlBuilder->select('Name')->from('People')->where('PersonID = :id')
            ->order('PersonID ASC')->result();
        $pdo = array('id' => $personID);
        $name = $this->database->run($sql, $pdo)[0]['Name'];

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(200, $response['code'], 'Response code was unexpected');
        $this->assertEquals(1, count($response['contents']), 'Response count was wrong');
        $this->assertEquals('UnitTest Person1', $name, 'Name is unexpected');
    }
}
