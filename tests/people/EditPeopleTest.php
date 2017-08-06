<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class EidtPeopleTest extends TestCase
{
    public function setUp()
    {
        $this->people = new \Apps\People\EditPeople();
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
        $this->assertTrue($this->people instanceof \Apps\People\EditPeople);
    }

    public function testEditingPeopleWorksWithObject()
    {
        $personID = $this->validPeopleIDs[0];
        $json = '{"name": "UnitTest Editor"}';
        $response = $this->people->edit($personID, $json);

        $sql = $this->sqlBuilder->select('Name')->from('People')->where('PersonID = :id')->result();
        $pdo = array('id' => $personID);
        $result = $this->database->run($sql, $pdo);
        $name = $result[0]['Name'];

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertTrue(is_array($response['contents']), 'Contents has wrong type');
        $this->assertEquals($name, 'UnitTest Editor', 'Name is wrong');
    }

    public function testEditingPeopleWorksWithArray()
    {
        $personID = $this->validPeopleIDs[0];
        $json = '[{"name": "UnitTest Editor"}]';
        $response = $this->people->edit($personID, $json);

        $sql = $this->sqlBuilder->select('Name')->from('People')->where('PersonID = :id')->result();
        $pdo = array('id' => $personID);
        $result = $this->database->run($sql, $pdo);
        $name = $result[0]['Name'];

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertTrue(is_array($response['contents']), 'Contents has wrong type');
        $this->assertEquals($name, 'UnitTest Editor', 'Name is wrong');
    }

    public function testEditingPeopleWithInvalidJSONIsBadRequest()
    {
        $personID = $this->validPeopleIDs[0];
        $json = '{invalid';
        $response = $this->people->edit($personID, $json);

        $this->assertEquals(400, $response['code']);
        $this->assertEquals('Invalid JSON', $response['contents'], 'Unexpected contents');
    }

    public function testEditingPeopleWithMultipleJSONObjectsIsBadRequest()
    {
        $personID = $this->validPeopleIDs[0];
        $json = '[{"name": "Test1"}, {"name": "Test2"}]';
        $response = $this->people->edit($personID, $json);

        $this->assertEquals(400, $response['code']);
        $this->assertEquals('You are only allowed to edit one person!', $response['contents']);
    }
}
