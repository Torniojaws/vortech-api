<?php

namespace VortechAPI\Tests\Contacts;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetContactsTest extends TestCase
{
    public function setUp()
    {
        $this->contacts = new \Apps\Contacts\GetContacts();

        $this->create = new \Apps\Database\Insert();
        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->arrays = new \Apps\Utils\Arrays();

        // Add contacts - we should get the newest one
        $contacts = new \Apps\Contacts\AddContacts();
        $json = '{"email": "unittest@example.com", "techrider": "techrider2017.pdf",
            "inputlist": "inputlist2017.pdf", "backline": "backline2017.pdf"}';
        $contacts->add($json);

        // Second
        $json = '{"email": "unittest2@example.com", "techrider": "techrider2017-2.pdf",
            "inputlist": "inputlist2017-2.pdf", "backline": "backline2017-2.pdf"}';
        $response = $contacts->add($json);
        $this->validID = $response['id'];
    }

    public function tearDown()
    {
        $sql = $this->delete->delete()->from('Contacts')->where('Email LIKE :email')->result();
        $pdo = array('email' => 'unittest%');
        $this->database->run($sql, $pdo);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->contacts instanceof \Apps\Contacts\GetContacts);
    }

    public function testGettingContactsReturnsTheNewestOne()
    {
        $response = $this->contacts->get();

        $contact = $response['contents'][0];

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertFalse(empty($response['contents']), 'No contents');
        $this->assertEquals('unittest2@example.com', $contact['Email'], 'Wrong email');
        $this->assertEquals($this->validID, $contact['ContactsID'], 'Wrong ID');
        $this->assertEquals('inputlist2017-2.pdf', $contact['InputList'], 'Wrong input');
    }
}
