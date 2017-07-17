<?php

namespace VortechAPI\Apps\News;

require_once('../database/database.php');
require_once('../database/select.php');
require_once('../database/insert.php');
require_once('../database/update.php');
require_once('../database/delete.php');
require_once('../utils/json.php');
require_once('../utils/arrays.php');

class News
{
    public function __construct()
    {
        $this->db = new \VortechAPI\Apps\Database\Database();
        // We always run a query when this class is instantiated, so let's connect automatically
        $this->db->connect();
        $this->buildSelect = new \VortechAPI\Apps\Database\BuildSelect();
        $this->buildInsert = new \VortechAPI\Apps\Database\BuildInsert();
        $this->buildUpdate = new \VortechAPI\Apps\Database\BuildUpdate();
        $this->buildDelete = new \VortechAPI\Apps\Database\BuildDelete();
        $this->jsonValidator = new \VortechAPI\Apps\Utils\JsonTools();
        $this->arrayUtils = new \VortechAPI\Apps\Utils\ArrayUtils();
    }

    public function getNews($params)
    {
        $response = array();
        $pdoParams = array();

        $sql = $this->buildSelect->select()->from('News')->result();

        // This gets a specific NewsID, eg. GET /news/1
        if (is_numeric($params[1])) {
            $sql = $this->buildSelect->select()->from('News')->where('NewsID = :id')->result();
            $pdoParams = array('id' => $params[1]);
        }

        // Now we can run the query, which uses a PDO prepared statement
        $results = $this->db->run($sql, $pdoParams);

        $response['contents'] = $results;
        $response['code'] = 200;
        return $response;
    }

    /**
     * Add a news item to the database, and insert the related categories to its own table
     * @param string $data Contains the raw input from php://input, which should be a JSON
     * @return array $response The array of the results
     */
    public function addNews($data)
    {
        $response = array();

        // Validate the JSON. Invalid JSON will result in a 400 Bad Request
        if ($this->jsonValidator->isJson($data) == false) {
            $response['contents'] = 'Invalid JSON';
            $response['code'] = 400;
            return $response;
        }

        // Then get the data from an associative array of the JSON
        $json = json_decode($data, true);
        $title = $json['title'];
        $contents = $json['contents'];
        $categories = $json['categories'];

        // Write the news to the DB
        $sql = $this->buildInsert->insert()->into('News(Title, Contents, Author, Created)')
            ->values(':title, :contents, "Juha", NOW()')->result();
        $pdoParams = array('title' => $title, 'contents' => $contents);

        // Now we can run the query, which uses a PDO prepared statement
        $results = $this->db->run($sql, $pdoParams);
        $currentNewsId = $this->db->getInsertId();

        // And then we write the categories of the news post
        foreach ($categories as $category) {
            $sql = $this->buildInsert->insert()->into('NewsCategories(NewsID, CategoryID)')
                ->values(':id, :category')->result();
            $pdoParams = array('id' => $currentNewsId, 'category' => $category);
            $results = $this->db->run($sql, $pdoParams);
        }

        $response['contents'] = "Location: http://www.vortechmusic.com/api/1.0/news/".$currentNewsId;
        $response['code'] = 201; // https://tools.ietf.org/html/rfc7231#section-4.3
        return $response;
    }

    public function editNews($params, $data)
    {
        $response = array();

        // We only proceed if we have a NewsID
        if (is_numeric($params[1]) == false) {
            $response['contents'] = 'Missing NewsID from URL';
            $response['code'] = 400;
            return $response;
        }

        // Validate the JSON. Invalid JSON will result in a 400 Bad Request
        if ($this->jsonValidator->isJson($data) == false) {
            $response['contents'] = 'Invalid JSON';
            $response['code'] = 400;
            return $response;
        }

        // Convert the JSON into an array and get the data
        $json = json_decode($data, true);
        $title = $json['title'];
        $contents = $json['contents'];
        $categories = $json['categories'];

        // Update the News entry
        $sql = $this->buildUpdate->update('News')->set('Title = :title, Contents = :contents, Updated = NOW()')
            ->where('NewsID = :id')->result();
        $pdoParams = array('id' => $params[1], 'title' => $title, 'contents' => $contents);

        // Now we can run the query, which uses a PDO prepared statement
        $this->db->run($sql, $pdoParams);

        // And categories. This is a bit tricky, since each entry has its own row in the table
        // So we check what exists already
        $sql = $this->buildSelect->select('DISTINCT(CategoryID)')->from('NewsCategories')->where('NewsID = :id')->result();
        $pdoParams = array('id' => $params[1]);
        $existingCategoryIds = $this->db->run($sql, $pdoParams);

        // The data is in an array of arrays, so let's convert it to a plain array(1, 2, 3)
        $flat = $this->arrayUtils->flattenArray($existingCategoryIds, 'CategoryID');
        $flatExisting = $this->arrayUtils->toIntArray($flat);

        // Then we iterate the new values (array of integers)
        foreach ($categories as $category) {
            // If the new category is not in the existingCategoryIds, we INSERT it
            if (in_array($category, $flatExisting) == false) {
                $sql = $this->buildInsert->insert()->into('NewsCategories(NewsID, CategoryID)')
                    ->values(':id, :category')->result();
                $pdoParams = array('id' => $params[1], 'category' => $category);
                $this->db->run($sql, $pdoParams);
                // To prevent duplicates, we add the new entry to the array
                $flatExisting[] = $category;
            }

            // If the existingCategoryId does not exist in the new categories, we DELETE it
            foreach ($flatExisting as $old) {
                if (in_array($old, $categories) == false) {
                    $sql = 'DELETE FROM NewsCategories
                            WHERE CategoryID = :id';
                    $pdoParams = array('id' => $old);
                    $this->db->run($sql, $pdoParams);
                }
            }
        }
        // All done
        $response['contents'] = "Location: http://www.vortechmusic.com/api/1.0/news/".$params[1];
        $response['code'] = 200;
        return $response;
    }

    public function deleteNews($params)
    {
        $response = array();

        // We only proceed if we have a NewsID
        if (is_numeric($params[1]) == false) {
            $response['contents'] = 'Missing NewsID from URL';
            $response['code'] = 400;
            return $response;
        }

        // There is a high chance that the item has foreign keys in NewsCategories, so they
        // must first be deleted
        $sql = $this->buildDelete->delete()->from('NewsCategories')->where('NewsID = :id')->result();
        $pdoParams = array('id' => $params[1]);
        $this->db->run($sql, $pdoParams);

        // Then we can delete the News post itself
        $sql = $this->buildDelete->delete()->from('News')->where('NewsID = :id')->result();
        $pdoParams = array('id' => $params[1]);
        $this->db->run($sql, $pdoParams);

        return http_response_code(204);
    }
}
