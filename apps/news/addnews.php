<?php

namespace Apps\News;

class AddNews
{
    public function __construct()
    {
        $this->database = new \Apps\Database\Database();
        $this->database->connect();
        $this->sql = new \Apps\Database\Insert();
    }

    public function add(string $json)
    {
        $news = json_decode($json, true);
        $title = $news['title'];
        $contents = $news['contents'];
        $categories = $news['categories'];

        $newsID = $this->insertNews($title, $contents);

        foreach ($categories as $category) {
            $this->insertCategory($category, $newsID);
        }

        return $this->response($newsID);
    }

    /**
     * Add the news item to database.
     * @param string $title Is the news title to use
     * @param string $contents Is the news text to use
     * @return int The NewsID of the news we inserted
     */
    public function insertNews(string $title, string $contents)
    {
        $sql = $this->sql->insert()->into('News(Title, Contents, Author, Created)')
            ->values(':title, :contents, "Juha", NOW()')->result();
        $pdo = array('title' => $title, 'contents' => $contents);
        $this->database->run($sql, $pdo);

        return $this->database->getInsertId();
    }

    /**
     * Insert the related categories of the news into their own table
     * @param int $category The ID of the category
     * @param int $newsID Is the ID we use as a foreign key to the news item
     */
    public function insertCategory(int $category, int $newsID)
    {
        $sql = $this->sql->insert()->into('NewsCategories(NewsID, CategoryID)')
            ->values(':id, :category')->result();
        $pdo = array('id' => $newsID, 'category' => $category);
        $this->database->run($sql, $pdo);
    }

    /**
     * Build a response for the request
     * @param int $newsID is the ID that we need to show in the response body Location
     * @return array That contains the response array
     */
    public function response(int $newsID)
    {
        $response['contents'] = "Location: http://www.vortechmusic.com/api/1.0/news/".$newsID;
        $response['id'] = $newsID;
        $response['code'] = 201; // https://tools.ietf.org/html/rfc7231#section-4.3

        return $response;
    }
}
