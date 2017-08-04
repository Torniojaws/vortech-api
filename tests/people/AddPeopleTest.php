<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddPeopleTest extends TestCase
{
    public function setUp()
    {
        $this->people = new \Apps\People\AddPeople();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();
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
        $this->assertTrue($this->people instanceof \Apps\People\AddPeople);
    }

    public function testAddingOnePerson()
    {
        $json = '{"name": "UnitTest Magic"}';
        $response = $this->people->add($json);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Name')->from('People')->where('Name = :name')->result();
        $pdo = array('name' => 'UnitTest Magic');
        $result = $this->database->run($sql, $pdo);

        $this->assertEquals(201, $response['code']);
        $this->assertFalse(empty($result));
        $this->assertEquals('UnitTest Magic', $result[0]['Name']);
    }

    public function testAddingMultiplePeople()
    {
        $json = '[{"name": "UnitTest Magic"}, {"name": "UnitTest Unicorns"}]';
        $response = $this->people->add($json);

        $sqlBuilder = new \Apps\Database\Select();
        $sql = $sqlBuilder->select('Name')->from('People')->where('Name LIKE :name')
            ->order('PersonID')->result();
        $pdo = array('name' => 'UnitTest%');
        $result = $this->database->run($sql, $pdo);

        $this->assertEquals(201, $response['code']);
        $this->assertFalse(empty($result));
        $this->assertEquals('UnitTest Magic', $result[0]['Name']);
        $this->assertEquals('UnitTest Unicorns', $result[1]['Name']);
    }

    public function testInvalidJSON()
    {
        $json = '{hehhee';
        $response = $this->people->add($json);

        $this->assertEquals(400, $response['code']);
    }
}
