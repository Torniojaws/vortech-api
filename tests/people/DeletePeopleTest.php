<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DeletePeopleTest extends TestCase
{
    public function setUp()
    {
        $this->deletePeople = new \Apps\People\DeletePeople();

        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        // Add some people
        $people = new \Apps\People\AddPeople();
        $json = '[{"name": "UnitTest Guitar"}, {"name": "UnitTest Bass"}]';
        $people->add($json);

        // Get the people's IDs
        $select = new \Apps\Database\Select();
        $sql = $select->select('PersonID')->from('People')->where('Name LIKE :name')->result();
        $pdo = array('name' => 'UnitTest%');
        $this->personIDs = $this->database->run($sql, $pdo);
    }

    public function tearDown()
    {
        // Just in case :)
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('People')->where('Name LIKE :name')->result();
        $pdo = array('name' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->deletePeople instanceof \Apps\People\DeletePeople);
    }

    public function testDeleteWorksWithValidID()
    {
        $this->deletePeople->delete($this->personIDs[0]['PersonID']);

        $check = new \Apps\Utils\DatabaseCheck();
        $exists = $check->existsInTable('People', 'PersonID', $this->personIDs[0]['PersonID']);

        $this->assertFalse($exists, 'PersonID exists in table! Should not.');
    }

    public function testDeleteReturnsExpectedResponseWithInvalidID()
    {
        $response = $this->deletePeople->delete(-26);

        $this->assertEquals($response['code'], 400);
    }
}
