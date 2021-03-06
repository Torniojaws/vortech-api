<?php

namespace Apps\News;

require_once(__DIR__.'/../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

header('Content-Type: application/json');

$request = new \Apps\Utils\Request($_SERVER, $_GET);
$method = $request->getMethod();
$json = file_get_contents('php://input');
$isValid = $request->isValid($json);

if ($isValid == false) {
    $response['contents'] = 'Invalid request';
    $response['code'] = 400;
}

switch ($method) {
    case 'GET':
        $news = new \Apps\News\GetNews();
        $newsID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
        $response = $news->get($newsID);
        break;
    case 'POST':
        if ($isValid) {
            $news = new \Apps\News\AddNews();
            $response = $news->add($json);
        }
        break;
    case 'PUT':
        if ($isValid) {
            $news = new \Apps\News\EditNews();
            $newsID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $news->edit($newsID, $json);
        }
        break;
    case 'PATCH':
        if ($isValid) {
            $news = new \Apps\News\PatchNews();
            $newsID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $news->patch($newsID, $json);
        }
        break;
    case 'DELETE':
        if ($isValid) {
            $news = new \Apps\News\DeleteNews();
            $newsID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $news->delete($newsID);
        }
        break;
    case 'OPTIONS':
        header('Allow: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response['contents'] = array('Allowed' => array('GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'));
        $response['code'] = 200;
        break;
    default:
        header('Allow: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response['contents'] = 'Not allowed';
        $response['code'] = 405;
        break;
}

echo json_encode($response['contents'], JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
return http_response_code($response['code']);
