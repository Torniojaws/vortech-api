<?php

namespace VortechAPI\Tests\Shop;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class AddShopTest extends TestCase
{
    public function setUp()
    {
        $this->shop = new \Apps\Shop\AddShop();

        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->arrays = new \Apps\Utils\Arrays();
    }

    public function tearDown()
    {
        $sql = $this->delete->delete()->from('ShopItems')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $sql = $this->delete->delete()->from('ShopItemImages')->where('Image like :img')->result();
        $pdo = array('img' => 'unittest-spotify.png');
        $this->database->run($sql, $pdo);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->shop instanceof \Apps\Shop\AddShop);
    }

    public function testAddingShopItemWorks()
    {
        // When "urls" contains an "image" with a string value, it means it is a new logo that was
        // uploaded during adding the shop item.
        $json = '{"title": "UnitTest Shop Item", "description": "This is a nice Unit Test",
            "price": {"value": "15.99", "currency": "EUR"}, "image": "shop-item.jpg",
            "categories": [1, 2], "urls": [
                {"title": "PayPal", "url": "http://www.paypal.com", "image": 1},
                {"title": "BandCamp", "url": "http://vortech.bandcamp.com", "image": 2},
                {"title": "UnitSpotify", "url": "http://www.spotify.com", "image": "unittest-spotify.png"}
            ]}';
        $response = $this->shop->add($json);

        $sql = $this->read->select()->from('ShopItems')->where('ShopItemID = :id')->result();
        $pdo = array('id' => $response['id']);
        $shopitem = $this->database->run($sql, $pdo)[0];

        $sql = $this->read->select()->from('ShopItemCategories')->where('ShopItemID = :id')
            ->order('ShopItemCategoryID ASC')->result();
        $queryArray = $this->database->run($sql, $pdo);
        $shopitemCategories = $this->arrays->flattenArray($queryArray, 'ShopCategoryID');

        $sql = $this->read->select()->from('ShopItemURLs')->where('ShopItemID = :id')
            ->order('ShopItemURLID ASC')->result();
        $shopitemUrls = $this->database->run($sql, $pdo);

        $sql = $this->read->select('Image')->from('ShopItemImages')->where('Image = :img')
            ->order('ShopItemImageID ASC')->result();
        $pdo = array('img' => 'unittest-spotify.png');
        $shopitemImages = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(201, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Shop Item', $shopitem['Title'], 'Wrong title found');
        $this->assertEquals(15.99, $shopitem['Price'], 'The price is wrong, bitch');
        $this->assertEquals(2, count($shopitemCategories), 'Wrong amount of categories added');
        $this->assertEquals(2, $shopitemCategories[1], 'Shop item category wrong');
        $this->assertEquals(3, count($shopitemUrls), 'Wrong amount of urls added');
        $this->assertTrue(isset($shopitemImages[0]), 'New image was not found in the table');
        $this->assertEquals('unittest-spotify.png', $shopitemImages[0]['Image'], 'New image was not added with correct filename');
        $this->assertEquals('http://vortech.bandcamp.com', $shopitemUrls[1]['URL'], 'Wrong URL for item');
    }

    public function testAddingShopItemAsArrayWorks()
    {
        // When "urls" contains an "image" with a string value, it means it is a new logo that was
        // uploaded during adding the shop item.
        $json = '[{"title": "UnitTest Shop Item", "description": "This is a nice Unit Test",
            "price": {"value": "15.99", "currency": "EUR"}, "image": "shop-item.jpg",
            "categories": [1, 2], "urls": [
                {"title": "PayPal", "url": "http://www.paypal.com", "image": 1},
                {"title": "BandCamp", "url": "http://vortech.bandcamp.com", "image": 2},
                {"title": "UnitSpotify", "url": "http://www.spotify.com", "image": "unittest-spotify.png"}
            ]}]';
        $response = $this->shop->add($json);

        $sql = $this->read->select()->from('ShopItems')->where('ShopItemID = :id')->result();
        $pdo = array('id' => $response['id']);
        $shopitem = $this->database->run($sql, $pdo)[0];

        $sql = $this->read->select()->from('ShopItemCategories')->where('ShopItemID = :id')
            ->order('ShopItemCategoryID ASC')->result();
        $queryArray = $this->database->run($sql, $pdo);
        $shopitemCategories = $this->arrays->flattenArray($queryArray, 'ShopCategoryID');

        $sql = $this->read->select()->from('ShopItemURLs')->where('ShopItemID = :id')
            ->order('ShopItemURLID ASC')->result();
        $shopitemUrls = $this->database->run($sql, $pdo);

        $sql = $this->read->select('Image')->from('ShopItemImages')->where('Image = :img')
            ->order('ShopItemImageID ASC')->result();
        $pdo = array('img' => 'unittest-spotify.png');
        $shopitemImages = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(201, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Shop Item', $shopitem['Title'], 'Wrong title found');
        $this->assertEquals(15.99, $shopitem['Price'], 'The price is wrong, bitch');
        $this->assertEquals(2, count($shopitemCategories), 'Wrong amount of categories added');
        $this->assertEquals(2, $shopitemCategories[1], 'Shop item category wrong');
        $this->assertEquals(3, count($shopitemUrls), 'Wrong amount of urls added');
        $this->assertTrue(isset($shopitemImages[0]), 'New image was not found in the table');
        $this->assertEquals('unittest-spotify.png', $shopitemImages[0]['Image'], 'New image was not added with correct filename');
        $this->assertEquals('http://vortech.bandcamp.com', $shopitemUrls[1]['URL'], 'Wrong URL for item');
    }

    public function testThatInvalidJSONIsBadRequest()
    {
        $json = '{invalid';
        $response = $this->shop->add($json);

        $this->assertFalse(empty($response), 'Response should not be empty');
        $this->assertEquals(400, $response['code'], 'Wrong response code');
    }
}
