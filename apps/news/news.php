<?php

/*
    This is generally used to handle News items. News will also have comments, which are stored
    in a different table than the news themselves. They are matched by news ID as foreign key.
*/

$method = $_SERVER['REQUEST_METHOD'];

// Get the possible parameters, eg. /news/123 has the parameter "123" for News ID
$parameters = explode('/', $_GET['params']);

// News ID, eg. GET /news/123
$id = $parameters[1];

// The action is generally reserved for getting the comments for a given news ID eg.
// GET /news/123/comments
// where "comments" is the action.
$action = $parameters[2];

$resp = array();

switch ($method) {
    case 'GET':
        // Either all news or a specific news ID
        $resp["news"] = "world";
        if (is_numeric($id)) {
            $resp["id"] = $id;
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

header('Content-Type: application/json');
echo json_encode($resp);
