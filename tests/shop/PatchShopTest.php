<?php

namespace VortechAPI\Tests\Shop;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class PatchShopTest extends TestCase
{
    public function setUp()
    {
        $this->shop = new \Apps\Shop\PatchShop();

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
        $this->assertTrue($this->shop instanceof \Apps\Shop\PatchShop);
    }

    public function testPatchingShopItems()
    {
        $json = '{"title": "UnitTest Patched", "price": {"value": "9.99"}, "urls": [
            {"title": "Amazon", "url": "http://www.amazon.com", "image": "amazon.png"}]}';
        $response = $this->shop->patch($this->anotherValidID, $json);

        $sql = $this->read->select()->from('ShopItems')->where('ShopItemID = :id')->result();
        $pdo = array('id' => $this->anotherValidID);
        $shopitem = $this->database->run($sql, $pdo)[0];

        $sql = $this->read->Select('Title')->from('ShopItemURLs')
            ->where('ShopItemID = :id AND Title = :title')->result();
        $pdo = array('id' => $this->anotherValidID, 'title' => 'Amazon');
        $urls = $this->database->run($sql, $pdo);

        $this->assertFalse(empty($response), 'Empty response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertEquals('UnitTest Patched', $shopitem['Title'], 'Title was not edited');
        $this->assertEquals('9.99', $shopitem['Price'], 'Price was not edited');
        $this->assertTrue(isset($urls[0]), 'Could not find expected url');
        $this->assertEquals('Amazon', $urls[0]['Title'], 'New url was not added');
    }
}
