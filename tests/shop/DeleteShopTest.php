<?php

namespace VortechAPI\Tests\Shop;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class DeleteShopTest extends TestCase
{
    public function setUp()
    {
        $this->shop = new \Apps\Shop\DeleteShop();

        $this->read = new \Apps\Database\Select();
        $this->delete = new \Apps\Database\Delete();
        $this->database = new \Apps\Database\Database();
        $this->database->connect();

        $this->arrays = new \Apps\Utils\Arrays();
        $this->dbCheck = new \Apps\Utils\DatabaseCheck();

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
        // Just in case...
        $sql = $this->delete->delete()->from('ShopItems')->where('Title LIKE :title')->result();
        $pdo = array('title' => 'UnitTest%');
        $this->database->run($sql, $pdo);

        $sql = $this->delete->delete()->from('ShopItemImages')->where('Image like :img')->result();
        $pdo = array('img' => 'unittest-spotify.png');
        $this->database->run($sql, $pdo);
    }

    public function testClassWorks()
    {
        $this->assertTrue($this->shop instanceof \Apps\Shop\DeleteShop);
    }

    public function testDeletingWorks()
    {
        $response = $this->shop->delete($this->validID);

        $itemExists = $this->dbCheck->existsInTable('ShopItems', 'ShopItemID', $this->validID);
        $urlsExist = $this->dbCheck->existsInTable('ShopItemURLs', 'ShopItemID', $this->validID);

        $this->assertFalse(empty($response), 'Response was empty');
        $this->assertEquals(204, $response['code'], 'Wrong response code');
        $this->assertFalse($itemExists, 'Shopitem was not deleted successfully');
        $this->assertFalse($urlsExist, 'Shopitem URLs were not deleted successfully');
    }

    public function testDeletingNonExistingIDIs404()
    {
        $response = $this->shop->delete(-30);

        $this->assertFalse(empty($response), 'Empty response');
        $this->assertEquals(404, $response['code'], 'Wrong response code');
    }
}
