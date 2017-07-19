<?php

namespace VortechAPI\Tests;

use PHPUnit\Framework\TestCase;

require_once(__DIR__.'/../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

class NewsHandlerTest extends TestCase
{
    public function __construct()
    {
        $this->news = new \Apps\News\NewsHandler();
    }

    /**
     * A bit silly, but needed for 100 % code coverage
     */
    public function testNewsHandlerConstructor()
    {
        $newsInstance = new \Apps\News\NewsHandler();

        $this->assertTrue(isset($newsInstance->db));
    }

    public function testGettingNewsWithIDHasCorrectDataStructure()
    {
        $params = array("ignored", 111);
        $result = $this->news->getNews($params);

        $this->assertTrue(array_key_exists('contents', $result));
    }

    public function testGettingNewsWithIDHasExpectedReturnCode()
    {
        $params = array("ignored", 15151);
        $result = $this->news->getNews($params);

        $this->assertTrue($result['code'] == 200);
    }

    public function testGettingNewsWithoutID()
    {
        $params = array(null, null);
        $result = $this->news->getNews($params);

        $this->assertTrue($result['code'] == 200);
    }

    public function testGettingNewsCommentsWithID()
    {
        // FIXME: This feature *might* be moved to a separate class soon
        $params = array("ignored", 1, "comments");
        $result = $this->news->getNews($params);

        $this->assertTrue(array_key_exists('contents', $result));
    }

    public function testAddingNews()
    {
        $json = '{"title": "Testcase", "contents": "Example", "categories": [1, 2]}';
        // This writes data to DB! It will be edited and removed in the tests below
        $response = $this->news->addNews($json);

        $this->assertTrue($response['code'] == 201);
    }

    public function testEditingNewsReturnsCorrectCode()
    {
        // Since class properties are cleared automatically between tests, we do it this way
        $params = array(null, null);
        $result = $this->news->getNews($params);
        $latestNews = end($result['contents']);

        $json = '{"title": "Testcase", "contents": "Example2", "categories": [3, 4]}';
        $params = array("ignore", $latestNews['NewsID']);
        $response = $this->news->editNews($params, $json);

        $this->assertTrue($response['code'] == 200);
    }

    public function testEditingNewsHasEditedData()
    {
        // Since class properties are cleared automatically between tests, we do it this way
        $params = array(null, null);
        $result = $this->news->getNews($params);
        $latestNews = end($result['contents']);

        $json = '{"title": "Testcase", "contents": "Example2", "categories": [3, 4]}';
        $params = array("ignore", $latestNews['NewsID']);
        $this->news->editNews($params, $json);

        $verification = $this->news->getNews(array("i", $latestNews['NewsID']));

        $this->assertTrue($verification['contents'][0]['Title'] == 'Testcase');
    }

    public function testEditingNewsWithInvalidID()
    {
        $params = array(null, null);
        $json = '{"title": "Testcase", "contents": "Example2", "categories": [3, 4]}';
        $response = $this->news->editNews($params, $json);

        $this->assertTrue($response['code'] == 400);
    }

    public function testDeleteNewsReturnsExpectedResult()
    {
        // Since class properties are cleared automatically between tests, we do it this way
        $params = array(null, null);
        $result = $this->news->getNews($params);
        $latestNews = end($result['contents']);

        $isDeleted = $this->news->deleteNews($latestNews['NewsID']);

        $this->assertTrue($isDeleted);
    }

    /**
     * XXX: Warning! Since there is no way to pass the NewsID from above to this one, as PHPUnit
     * resets the test class between test methods, we need to rely that there are no leftovers from
     * previous tests. FIXME: Is there some better way?
     */
    public function testDeleteNewsHasDeletedNews()
    {
        // Since class properties are cleared automatically between tests, we do it this way
        $params = array(null, null);
        $result = $this->news->getNews($params);
        $latestNews = end($result['contents']);

        $params = array("i", $latestNews['NewsID']);
        $response = $this->news->getNews($params);

        $this->assertTrue($response['contents'] !== 'Example2');
    }
}
