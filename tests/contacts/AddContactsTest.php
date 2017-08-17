<?php

namespace VortechAPI\Tests\Contacts;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddContactsTest extends TestCase
{
    public function setUp()
    {
        $this->contacts = new \Apps\Contacts\AddContacts();

        $this->create = new \Apps\Database\Insert();
        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->arrays = new \Apps\Utils\Arrays();
    }

    public function tearDown()
    {
        $sql = $this->delete->delete()->from('Contacts')->where('Email LIKE :email')->result();
        $pdo = array('email' => 'unittest%');
        $this->database->run($sql, $pdo);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->contacts instanceof \Apps\Contacts\AddContacts);
    }

    public function testAddingNewContact()
    {
        $json = '{"email": "unittest@example.com", "techrider": "techrider2017.pdf",
            "inputlist": "inputlist2017.pdf", "backline": "backline2017.pdf"}';
        $response = $this->contacts->add($json);

        $sql = $this->read->select()->from('Contacts')->order('ContactsID ASC')->limit(1)->result();
        $pdo = array();
        $newest = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(201, $response['code'], 'Wrong response code');
        $this->assertTrue(is_numeric($response['id']), 'Insert ID is not a number');
        $this->assertFalse(empty($newest[0]), 'Nothing found in database table');
        $this->assertEquals('unittest@example.com', $newest[0]['Email'], 'Wrong email address');
    }

    public function testAddingNewContactUsingArray()
    {
        $json = '[{"email": "unittest@example.com", "techrider": "techrider2017.pdf",
            "inputlist": "inputlist2017.pdf", "backline": "backline2017.pdf"}]';
        $response = $this->contacts->add($json);

        $sql = $this->read->select()->from('Contacts')->order('ContactsID ASC')->limit(1)->result();
        $pdo = array();
        $newest = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(201, $response['code'], 'Wrong response code');
        $this->assertTrue(is_numeric($response['id']), 'Insert ID is not a number');
        $this->assertFalse(empty($newest[0]), 'Nothing found in database table');
        $this->assertEquals('techrider2017.pdf', $newest[0]['TechRider'], 'Wrong techrider filename');
    }

    public function testAddingContactWithInvalidJSON()
    {
        $json = '{invalid';
        $response = $this->contacts->add($json);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(400, $response['code'], 'Wrong response code');
        $this->assertEquals(-1, $response['id'], 'Unexpected contact ID');
    }
}
