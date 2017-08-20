<?php

namespace VortechAPI\Tests\Subscribers;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchSubscribersTest extends TestCase
{
    public function setUp()
    {
        $this->sub = new \Apps\Subscribers\PatchSubscribers();

        $this->update = new \Apps\Database\Update();
        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->arrays = new \Apps\Utils\Arrays();

        // Add subscriber
        $subs = new \Apps\Subscribers\AddSubscribers();
        $json = '{"email": "unittest@example.com"}';
        $response = $subs->add($json);
        $this->validID = $response['id'];
        $this->validIDs[] = $this->validID;

        // Add a second subscriber
        $json = '{"email": "unittest2@example.com"}';
        $response = $subs->add($json);
        $this->anotherValidID = $response['id'];
        $this->validIDs[] = $this->anotherValidID;
    }

    public function tearDown()
    {
        $sql = $this->delete->delete()->from('Subscribers')->where('Email LIKE :email')->result();
        $pdo = array('email' => 'unittest%');
        $this->database->run($sql, $pdo);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->sub instanceof \Apps\Subscribers\PatchSubscribers);
    }

    public function testPatchingWithJsonObjectWorks()
    {
        $json = '{"email": "unittest-patch@example.com"}';
        $response = $this->sub->patch($this->validID, $json);

        $sql = $this->read->select()->from('Subscribers')->where('SubscriberID = :id')->result();
        $pdo = array('id' => $this->validID);
        $patched = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('unittest-patch@example.com', $patched[0]['Email'], 'Email was not patched');
    }

    public function testPatchingWithJsonArrayWorks()
    {
        $json = '[{"email": "unittest-patch@example.com"}]';
        $response = $this->sub->patch($this->validID, $json);

        $sql = $this->read->select()->from('Subscribers')->where('SubscriberID = :id')->result();
        $pdo = array('id' => $this->validID);
        $patched = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('unittest-patch@example.com', $patched[0]['Email'], 'Email was not patched');
    }

    public function testPatchingWithInvalidJSONIsBadRequest()
    {
        $json = '{invalid';
        $response = $this->sub->patch($this->validID, $json);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(400, $response['code'], 'Wrong response code');
    }
}
