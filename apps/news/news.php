<?php

header('Content-Type: application/json');
require_once('../database/database.php');

// Get request information
$method = $_SERVER['REQUEST_METHOD'];
$parameters = explode('/', $_GET['params']);

// Assign the request params
$id = $parameters[1];
$action = $parameters[2];

// We store the response into this variable
$resp = array();

// Let's connect to the database
$db = new Database();
$db->connect();

// We get the SQL query as prepared statement from here
switch ($method) {
    case 'GET':
        if (is_numeric($id)) {
            // Get specific news post
            $sql = 'SELECT * FROM News WHERE NewsID = :id';
            $params = array("id" => $id);
        } else {
            // Get all news posts
            $sql = 'SELECT * FROM News';
            $params = array();
        }
        break;
    case 'POST':
        // Add news
        $resp = array("post" => "manpat");
        break;
    case 'PUT':
        // Update an existing news ID, eg. PUT /news/123
        // with a payload JSON that has the update information
        if (is_numeric($id) == false) {
            $resp["status"] = "error";
            $resp["reason"] = "Missing ID";
        } else {
            $resp = array("status" => "success");
            $resp["id"] = $id;
        }
        break;
    case 'DELETE':
        // Delete a news ID, eg. DELETE /news/123
        if (is_numeric($id) == false) {
            $resp["status"] = "error";
            $resp["reason"] = "Missing ID";
            return http_response_code(404);
        } else {
            return http_response_code(204);
        }
        break;
    default:
        $resp = array("dafuq" => "No idea what you tried");
        break;
}

// And then we run the prepared statement
$results = $db->run($sql, $params);
$db->close();

echo json_encode($results, JSON_NUMERIC_CHECK);
