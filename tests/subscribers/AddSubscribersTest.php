<?php

namespace VortechAPI\Tests\Biography;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddSubscribersTest extends TestCase
{
    public function setUp()
    {
        $this->sub = new \Apps\Subscribers\AddSubscribers();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->read = new \Apps\Database\Select();
        $this->update = new \Apps\Database\Update();
    }

    public function tearDown()
    {
        $sqlBuilder = new \Apps\Database\Delete();
        $sql = $sqlBuilder->delete()->from('Subscribers')->where('Email LIKE :email')->result();
        $pdo = array('email' => 'unittest%');
        $this->database->run($sql, $pdo);
        $this->database->close();
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->sub instanceof \Apps\Subscribers\AddSubscribers);
    }

    public function testAddingSubscriptionWorks()
    {
        $json = '{"email": "unittest@example.com"}';
        $response = $this->sub->add($json);

        $sql = $this->read->select()->from('Subscribers')->where('Email LIKE :email')->result();
        $pdo = array('email' => 'unittest%');
        $result = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(201, $response['code'], 'Wrong response');
        $this->assertFalse(empty($result[0]), 'No results from DB');
        $this->assertEquals('unittest@example.com', $result[0]['Email'], 'Email is wrong');
        $this->assertEquals(1, $result[0]['Active'], 'Subscription is not active');
    }

    public function testAddingSubscriptionWorksWithJSONArray()
    {
        $json = '[{"email": "unittest@example.com"}]';
        $response = $this->sub->add($json);

        $sql = $this->read->select()->from('Subscribers')->where('Email LIKE :email')->result();
        $pdo = array('email' => 'unittest%');
        $result = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(201, $response['code'], 'Wrong response');
        $this->assertFalse(empty($result[0]), 'No results from DB');
        $this->assertEquals('unittest@example.com', $result[0]['Email'], 'Email is wrong');
        $this->assertEquals(1, $result[0]['Active'], 'Subscription is not active');
    }

    public function testInvalidJSONIsBadRequest()
    {
        $json = '{invalid';
        $response = $this->sub->add($json);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(400, $response['code'], 'Wrong response');
    }

    public function testAddingSubscriberThatIsAlreadyInactiveWillActivateExisting()
    {
        // Subscribe first
        $json = '{"email": "unittest@example.com"}';
        $response = $this->sub->add($json);
        $validID = $response['id'];

        // Then unsubscribe for the test
        $sql = $this->update->update('Subscribers')->set('Active = 0')->where('SubscriberID = :id')
            ->result();
        $pdo = array('id' => $validID);
        $this->database->run($sql, $pdo);

        // And then subscribe again using the same email
        $response = $this->sub->add($json);

        $sql = $this->read->select()->from('Subscribers')->where('Email LIKE :email')->result();
        $pdo = array('email' => 'unittest@example%');
        $result = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'No response');
        $this->assertEquals(201, $response['code'], 'Wrong response code');
        $this->assertEquals(1, count($result), 'Duplicate email found');
        $this->assertEquals(1, $result[0]['Active'], 'Email was not reactivated');
    }
}
