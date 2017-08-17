<?php

namespace VortechAPI\Tests\Contacts;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchContactsTest extends TestCase
{
    public function setUp()
    {
        $this->contacts = new \Apps\Contacts\PatchContacts();

        $this->create = new \Apps\Database\Insert();
        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->arrays = new \Apps\Utils\Arrays();

        // Add contacts
        $contacts = new \Apps\Contacts\AddContacts();
        $json = '{"email": "unittest@example.com", "techrider": "techrider2017.pdf",
            "inputlist": "inputlist2017.pdf", "backline": "backline2017.pdf"}';
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
        $this->assertTrue($this->contacts instanceof \Apps\Contacts\PatchContacts);
    }

    public function testPatchingWorks()
    {
        $json = '{"email": "unittest-patch@example.com"}';
        $response = $this->contacts->patch($this->validID, $json);

        // We only care of the latest contacts row, so just get that
        $contacts = new \Apps\Contacts\GetContacts();
        $result = $contacts->get();
        $newest = $result['contents'][0];

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertFalse(empty($newest), 'No contacts found');
        $this->assertEquals('unittest-patch@example.com', $newest['Email'], 'Wrong email');
    }

    public function testPatchingMultipleItemsWorks()
    {
        $json = '{"email": "unittest-patch@example.com", "techrider": "techrider2017-2.pdf"}';
        $response = $this->contacts->patch($this->validID, $json);

        // We only care of the latest contacts row, so just get that
        $contacts = new \Apps\Contacts\GetContacts();
        $result = $contacts->get();
        $newest = $result['contents'][0];

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertFalse(empty($newest), 'No contacts found');
        $this->assertEquals('unittest-patch@example.com', $newest['Email'], 'Wrong email');
        $this->assertEquals('techrider2017-2.pdf', $newest['TechRider'], 'Wrong techrider');
    }

    public function testPatchingWithArrayWorks()
    {
        $json = '[{"email": "unittest-patch@example.com", "techrider": "techrider2017-2.pdf"}]';
        $response = $this->contacts->patch($this->validID, $json);

        // We only care of the latest contacts row, so just get that
        $contacts = new \Apps\Contacts\GetContacts();
        $result = $contacts->get();
        $newest = $result['contents'][0];

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertFalse(empty($newest), 'No contacts found');
        $this->assertEquals('unittest-patch@example.com', $newest['Email'], 'Wrong email');
        $this->assertEquals('techrider2017-2.pdf', $newest['TechRider'], 'Wrong techrider');
    }
}
