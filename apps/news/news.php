<?php

namespace VortechAPI\Apps\News;

require_once('../utils/request.php');
require_once('News.class.php');

use VortechAPI\Apps\Utils\RequestHandler;
use VortechAPI\Apps\News\News;

header('Content-Type: application/json');

$news = new News();
$request = new RequestHandler($_SERVER, $_GET);

switch ($request->getMethod()) {
    case 'GET':
        $response = $news->getNews($request->getParams());
        break;
    case 'POST':
        $data = file_get_contents("php://input");
        $response = $news->addNews($data);
        break;
    case 'PUT':
        $data = file_get_contents("php://input");
        $response = $news->editNews($request->getParams(), $data);
        break;
    case 'DELETE':
        break;
    default:
        break;
}

echo json_encode($response["contents"], JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
return http_response_code($response["code"]);
