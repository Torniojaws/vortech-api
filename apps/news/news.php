<?php

header('Content-Type: application/json');

require_once('../database/database.php');
require_once('../utils/arrays.php');

// Get request information
$method = $_SERVER['REQUEST_METHOD'];
$parameters = explode('/', $_GET['params']);

// Assign the request params
if (count($parameters > 0)) {
    $id = $parameters[1];
    $action = $parameters[2];
}

// Let's connect to the database
$db = new Database();
$db->connect();

// We get the SQL query as prepared statement from here
switch ($method) {
    case 'GET':
        if (is_numeric($id)) {
            // Get specific news post
            $sql = 'SELECT *
                    FROM News
                    WHERE NewsID = :id';
            $params = array("id" => $id);
        } else {
            // Get all news posts
            $sql = 'SELECT *
                    FROM News';
            $params = array();
        }
        break;
    case 'POST':
        // Convert POST body JSON to an array
        $raw = file_get_contents("php://input");
        $json = json_decode($raw, true);

        // Then get the data from it
        $title = $json['title'];
        $contents = $json['contents'];
        $categories = $json['categories'];

        // Let's write the news to the DB already here, since Categories needs this to exist
        $sql = 'INSERT INTO News(Title, Contents, Author, Created)
                VALUES (:title, :contents, "Juha", NOW())';
        $params = array("title" => $title, "contents" => $contents);
        $db->run($sql, $params);
        $currentNewsId = $db->getInsertId();

        // Categories is a special thing that is written to another table that has a foreign key
        // We can have 1...* entries from Categories, and each is written to NewsCategories
        // Since the values are selected from a predefined set, we don't need to validate the ID
        // against the NewsCategories table
        foreach ($categories as $category) {
            $sql = 'INSERT INTO NewsCategories(NewsID, CategoryID)
                    VALUES (:id, :category)';
            $params = array("id" => $currentNewsId, "category" => $category);
            $db->run($sql, $params);
        }

        // Because we have already inserted everything we need, we can stop here
        return http_response_code(200)
    case 'PUT':
        // Update an existing news ID, eg. PUT /news/123
        // with a payload JSON that has the update information
        if (is_numeric($id) == false) {
            $resp["status"] = "error";
            $resp["reason"] = "Missing ID";
            echo json_encode($resp, JSON_NUMERIC_CHECK);
            return http_response_code(400);
        }

        // Convert POST body JSON to an array
        $raw = file_get_contents("php://input");
        $json = json_decode($raw, true);

        // Then get the data from it
        $title = $json['title'];
        $contents = $json['contents'];
        $categories = $json['categories'];

        // Let's update the News
        $sql = 'UPDATE News
                SET
                    Title = :title,
                    Contents = :contents,
                    Updated = NOW()
                WHERE
                    NewsID = :id';
        $params = array("id" => $id, "title" => $title, "contents" => $contents);
        $db->run($sql, $params);

        // And categories. This is a bit tricky, since each entry has its own row in the table
        // So we check what exists already
        $sql = 'SELECT DISTINCT(CategoryID)
                FROM NewsCategories
                WHERE NewsID = :id';
        $params = array("id" => $id);
        $existingCategoryIds = $db->run($sql, $params);
        // The data is in an array of arrays, so let's convert it to a plain array(1, 2, 3)
        $arrayUtils = new ArrayUtils();
        $arrayUtils->flattenArray($existingCategoryIds);
        $flatExisting = $arrayUtils->toIntArray();

        // Then we iterate the new values (array of integers)
        foreach ($categories as $category) {
            // If the new category is not in the existingCategoryIds, we INSERT it
            if (in_array($category, $flatExisting) == false) {
                $sql = 'INSERT INTO NewsCategories(NewsID, CategoryID)
                        VALUES (:id, :category)';
                $params = array("id" => $id, "category" => $category);
                $db->run($sql, $params);
                // To prevent duplicates, we add the new entry to the array
                $flatExisting[] = $category;
            }

            // If the existingCategoryId does not exist in the new categories, we DELETE it
            foreach ($flatExisting as $old) {
                if (in_array($old, $categories) == false) {
                    $sql = 'DELETE FROM NewsCategories
                            WHERE CategoryID = :id';
                    $params = array("id" => $old);
                    $db->run($sql, $params);
                }
            }
        }
        // All done, we can return now
        return http_response_code(200);
    case 'DELETE':
        // Delete a news ID, eg. DELETE /news/123
        if (is_numeric($id) == false) {
            $resp["status"] = "error";
            $resp["reason"] = "Missing ID";
            echo json_encode($resp, JSON_NUMERIC_CHECK);
            return http_response_code(400);
        }

        // There is a high chance that the item has foreign keys in NewsCategories, so they
        // must first be deleted
        $sql = 'DELETE FROM NewsCategories
                WHERE NewsID = :id';
        $params = array("id" => $id);
        $db->run($sql, $params);

        // Then we can delete the News post itself
        $sql = 'DELETE FROM News
                WHERE NewsID = :id';
        $params = array("id" => $id);
        $db->run($sql, $params);
        return http_response_code(204);
    default:
        $resp = array("dafuq" => "No idea what you tried");
        echo json_encode($resp, JSON_NUMERIC_CHECK);
        return http_response_code(400);
}

// And then we run the prepared statement
$results = $db->run($sql, $params);
$db->close();

echo json_encode($results, JSON_NUMERIC_CHECK);
