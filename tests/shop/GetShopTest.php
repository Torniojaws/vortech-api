<?php

namespace VortechAPI\Tests\Shop;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class GetShopTest extends TestCase
{
    public function setUp()
    {
        $this->shop = new \Apps\Shop\GetShop();

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
        $this->assertTrue($this->shop instanceof \Apps\Shop\GetShop);
    }

    public function testGettingOneShopItemsWorks()
    {
        $response = $this->shop->get($this->validID);

        $shopitem = $response['contents'];

        $this->assertFalse(empty($response), 'Got empty response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertFalse(empty($shopitem), 'No contents');
        $this->assertEquals('UnitTest Shop Item', $shopitem['title'], 'Wrong title');
        $this->assertEquals(1, $shopitem['categories'][0], 'Wrong category');
        $this->assertEquals('PayPal', $shopitem['urls'][0]['title']);
    }

    public function testGettingAllShopItemsWorks()
    {
        $response = $this->shop->get();

        $shopitems = $response['contents'];

        $this->assertFalse(empty($response), 'Got empty response');
        $this->assertEquals(200, $response['code'], 'Wrong response code');
        $this->assertFalse(empty($shopitems), 'No contents');
        $this->assertEquals('UnitTest Shop Item', $shopitems[0]['title'], 'Wrong title in first item');
        $this->assertEquals('UnitTest Shop Item 2', $shopitems[1]['title'], 'Wrong title in second item');
        $this->assertEquals(1, $shopitems[0]['categories'][0], 'Wrong category in first item');
        $this->assertEquals(3, $shopitems[1]['categories'][0], 'Wrong category in second item');
        $this->assertEquals('PayPal', $shopitems[0]['urls'][0]['title'], 'Wrong title in first item url');
        $this->assertEquals('BandCamp', $shopitems[1]['urls'][1]['title'], 'Wrong title in second item url');
    }
}
