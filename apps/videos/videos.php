<?php

namespace Apps\Videos;

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
        $videos = new \Apps\Videos\GetVideos();
        $videoID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
        $response = $videos->get($videoID);
        break;
    case 'POST':
        if ($isValid) {
            $videos = new \Apps\Videos\AddVideos();
            $response = $videos->add($json);
        }
        break;
    case 'PUT':
        if ($isValid) {
            $videos = new \Apps\Videos\EditVideo();
            $videoID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $videos->edit($videoID, $json);
        }
        break;
    case 'PATCH':
        if ($isValid) {
            $videos = new \Apps\Videos\PatchVideos();
            $videoID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $videos->patch($videoID, $json);
        }
        break;
    case 'DELETE':
        if ($isValid) {
            $videos = new \Apps\Videos\DeleteVideo();
            $videoID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $videos->delete($videoID);
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
