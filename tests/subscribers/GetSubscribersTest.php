<?php

namespace VortechAPI\Tests\Subscribers;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetSubscribersTest extends TestCase
{
    public function setUp()
    {
        $this->sub = new \Apps\Subscribers\GetSubscribers();

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
        $this->assertTrue($this->sub instanceof \Apps\Subscribers\GetSubscribers);
    }

    public function testGettingSubscribersWorks()
    {
        $response = $this->sub->get();

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertFalse(empty($response['contents']), 'No contents');
        $this->assertEquals('unittest@example.com', $response['contents'][0]['Email'], 'Wrong email');
        $this->assertTrue(isset($response['contents'][0]['UniqueID']), 'No unique ID');
        $this->assertEquals(2, count($response['contents']), 'Wrong amount of subscribers');
    }

    public function testGettingSubscriberUsingIDWorks()
    {
        // Using ID, we don't care if they are active or not. Let's unsubscribe for the test
        $sql = $this->update->update('Subscribers')->set('Active = 0')->where('SubscriberID = :id')
            ->result();
        $pdo = array('id' => $this->anotherValidID);
        $this->database->run($sql, $pdo);

        $response = $this->sub->get($this->anotherValidID);

        $sql = $this->read->select()->from('Subscribers')->where('SubscriberID = :id')->result();
        $pdo = array('id' => $this->anotherValidID);
        $actual = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertFalse(empty($response['contents']), 'No contents');
        $this->assertEquals($actual[0]['Email'], $response['contents'][0]['Email'], 'Wrong email');
        $this->assertTrue(isset($response['contents'][0]['UniqueID']), 'No unique ID');
        $this->assertEquals(1, count($response['contents']), 'Wrong amount of subscribers');
    }

    public function testGettingAllSubscribersDoesNotReturnInactiveOnesWorks()
    {
        // Let's unsubscribe for the test
        $sql = $this->update->update('Subscribers')->set('Active = 0')->where('SubscriberID = :id')
            ->result();
        $pdo = array('id' => $this->anotherValidID);
        $this->database->run($sql, $pdo);

        $response = $this->sub->get();

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertFalse(empty($response['contents']), 'No contents');
        $this->assertEquals('unittest@example.com', $response['contents'][0]['Email'], 'Wrong email');
        $this->assertTrue(isset($response['contents'][0]['UniqueID']), 'No unique ID');
        $this->assertEquals(1, count($response['contents']), 'Wrong amount of subscribers');
    }
}
