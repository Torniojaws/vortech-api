<?php

namespace VortechAPI\Tests\Shop;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class EditShopTest extends TestCase
{
    public function setUp()
    {
        $this->shop = new \Apps\Shop\EditShop();

        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->arrays = new \Apps\Utils\Arrays();

        // Add a shop item
        $json = '{"title": "UnitTest Shop Item", "description": "This is a nice Unit Test",
            "price": {"value": "15.99", "currency": "EUR"}, "image": "shop-item.jpg",
            "categories": [1, 2], "urls": [
                {"title": "PayPal", "url": "http://www.paypal.com", "image": 1},
                {"title": "BandCamp", "url": "http://vortech.bandcamp.com", "image": 2},
                {"title": "UnitSpotify", "url": "http://www.spotify.com", "image": "unittest-spotify.png"}
            ]}';
        $shop = new \Apps\Shop\AddShop();
        $response = $shop->add($json);
        $this->validID = $response['id'];
        $this->allValidIDs[] = $this->validID;

        // Add a second shop item
        $json = '{"title": "UnitTest Shop Item 2", "description": "This is a nice Unit Test 2",
            "price": {"value": "17.49", "currency": "EUR"}, "image": "shop-item2.jpg",
            "categories": [3, 4], "urls": [
                {"title": "PayPal", "url": "http://www.paypal.com", "image": 1},
                {"title": "BandCamp", "url": "http://vortech.bandcamp.com", "image": 2},
                {"title": "UnitSpotify", "url": "http://www.spotify.com", "image": "unittest-spotify.png"}
            ]}';
        $response = $shop->add($json);
        $this->anotherValidID = $response['id'];
        $this->allValidIDs[] = $this->anotherValidID;
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
        $this->assertTrue($this->shop instanceof \Apps\Shop\EditShop);
    }

    public function testEditingShopItemWorks()
    {
        $json = '{"title": "UnitTest Edited", "description": "This is a nice Unit Test edit",
            "price": {"value": "15.49", "currency": "EUR"}, "image": "shop-item2b.jpg",
            "categories": [3, 5], "urls": [
                {"title": "PayPal2", "url": "http://www.paypal2.com", "image": 1},
                {"title": "BandCamp", "url": "http://vortech.bandcamp.com", "image": 2},
                {"title": "UnitSpotify", "url": "http://www.spotify.com", "image": "unittest-spotify.png"}
            ]}';
        $response = $this->shop->edit($this->validID, $json);

        $sql = $this->read->select()->from('ShopItems')->where('ShopItemID = :id')->result();
        $pdo = array('id' => $this->validID);
        $shopitem = $this->database->run($sql, $pdo)[0];

        $sql = $this->read->select('ShopCategoryID')->from('ShopItemCategories')
            ->where('ShopItemID = :id')->order('ShopCategoryID ASC')->result();
        $result = $this->database->run($sql, $pdo);
        $categories = $this->arrays->flattenArray($result, 'ShopCategoryID');

        $sql = $this->read->select()->from('ShopItemURLs')->where('ShopItemID = :id')
            ->order('ShopItemURLID ASC')->result();
        $urls = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Edited', $shopitem['Title'], 'Title was not edited');
        $this->assertEquals('15.49', $shopitem['Price'], 'Price was not edited');
        $this->assertFalse(empty($shopitem['Updated']), 'Updated date was not set');
        $this->assertEquals(5, $categories[1], 'Category was not edited');
        $this->assertEquals('PayPal2', $urls[0]['Title'], 'Url title was not edited');
        $this->assertEquals('http://www.paypal2.com', $urls[0]['URL'], 'Url was not edited');
    }

    public function testEditingShopItemWorksWithArray()
    {
        $json = '[{"title": "UnitTest Edited", "description": "This is a nice Unit Test edit",
            "price": {"value": "15.49", "currency": "EUR"}, "image": "shop-item2b.jpg",
            "categories": [3, 5], "urls": [
                {"title": "PayPal2", "url": "http://www.paypal2.com", "image": 1},
                {"title": "BandCamp", "url": "http://vortech.bandcamp.com", "image": 2},
                {"title": "UnitSpotify", "url": "http://www.spotify.com", "image": "unittest-spotify.png"}
            ]}]';
        $response = $this->shop->edit($this->validID, $json);

        $sql = $this->read->select()->from('ShopItems')->where('ShopItemID = :id')->result();
        $pdo = array('id' => $this->validID);
        $shopitem = $this->database->run($sql, $pdo)[0];

        $sql = $this->read->select('ShopCategoryID')->from('ShopItemCategories')
            ->where('ShopItemID = :id')->order('ShopCategoryID ASC')->result();
        $result = $this->database->run($sql, $pdo);
        $categories = $this->arrays->flattenArray($result, 'ShopCategoryID');

        $sql = $this->read->select()->from('ShopItemURLs')->where('ShopItemID = :id')
            ->order('ShopItemURLID ASC')->result();
        $urls = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Edited', $shopitem['Title'], 'Title was not edited');
        $this->assertEquals('15.49', $shopitem['Price'], 'Price was not edited');
        $this->assertFalse(empty($shopitem['Updated']), 'Updated date was not set');
        $this->assertEquals(5, $categories[1], 'Category was not edited');
        $this->assertEquals('PayPal2', $urls[0]['Title'], 'Url title was not edited');
        $this->assertEquals('http://www.paypal2.com', $urls[0]['URL'], 'Url was not edited');
    }

    public function testInvalidJSONIsBadRequest()
    {
        $json = '{invalid';
        $response = $this->shop->edit($this->validID, $json);

        $this->assertFalse(empty($response), 'Empty response');
        $this->assertEquals(400, $response['code'], 'Wrong response code');
    }
}
