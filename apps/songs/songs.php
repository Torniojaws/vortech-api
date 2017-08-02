<?php

namespace Apps\Songs;

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
        $songs = new \Apps\Songs\GetSongs();
        $songID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
        $response = $songs->get($songID);
        break;
    case 'POST':
        if ($isValid) {
            $songs = new \Apps\Songs\AddSongs();
            $response = $songs->add($json);
        }
        break;
    case 'PUT':
        if ($isValid) {
            $songs = new \Apps\Songs\EditSongs();
            $newsID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $songs->edit($newsID, $json);
        }
        break;
    case 'PATCH':
        if ($isValid) {
            $songs = new \Apps\Songs\PatchSongs();
            $newsID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $songs->patch($newsID, $json);
        }
        break;
    case 'OPTIONS':
        header('Allow: GET, POST, PUT, PATCH, OPTIONS');
        $response['contents'] = array('Allowed' => array('GET', 'POST', 'PUT', 'PATCH', 'OPTIONS'));
        $response['code'] = 200;
        break;
    default:
        header('Allow: GET, POST, PUT, PATCH, OPTIONS');
        $response['contents'] = 'Not allowed';
        $response['code'] = 405;
        break;
}

echo json_encode($response['contents'], JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
return http_response_code($response['code']);
