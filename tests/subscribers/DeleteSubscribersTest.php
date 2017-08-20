<?php

namespace VortechAPI\Tests\Subscribers;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DeleteSubscribersTest extends TestCase
{
    public function setUp()
    {
        $this->sub = new \Apps\Subscribers\DeleteSubscribers();

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

        $sql = $this->read->select('UniqueID')->from('Subscribers')->where('SubscriberID = :id')
            ->result();
        $pdo = array('id' => $this->validID);
        $result = $this->database->run($sql, $pdo);
        $this->validUUID = $result[0]['UniqueID'];

        // Add a second subscriber
        $json = '{"email": "unittest2@example.com"}';
        $response = $subs->add($json);
        $this->anotherValidID = $response['id'];

        $sql = $this->read->select('UniqueID')->from('Subscribers')->where('SubscriberID = :id')
            ->result();
        $pdo = array('id' => $this->validID);
        $result = $this->database->run($sql, $pdo);
        $this->anotherValidUUID = $result[0]['UniqueID'];
    }

    public function tearDown()
    {
        $sql = $this->delete->delete()->from('Subscribers')->where('Email LIKE :email')->result();
        $pdo = array('email' => 'unittest%');
        $this->database->run($sql, $pdo);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->sub instanceof \Apps\Subscribers\DeleteSubscribers);
    }

    public function testDeletingUnsubscribes()
    {
        $response = $this->sub->delete($this->validID, $this->validUUID);

        $sql = $this->read->select()->from('Subscribers')->where('SubscriberID = :id')->result();
        $pdo = array('id' => $this->validID);
        $result = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(204, $response['code'], 'Wrong response code');
        $this->assertEquals(0, $result[0]['Active'], 'Email was not unsubscribed');
    }
}
