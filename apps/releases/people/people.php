<?php

namespace Apps\Releases\People;

require_once(__DIR__.'/../../../autoloader.php');
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
        if ($isValid) {
            $people = new \Apps\Releases\People\GetPeople();
            $releaseID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $people->get($releaseID);
        }
        break;
    case 'PATCH':
        if ($isValid) {
            $people = new \Apps\Releases\People\PatchPeople();
            $releaseID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $people->patch($releaseID, $json);
        }
        break;
    case 'OPTIONS':
        header('Allow: GET, PATCH, OPTIONS');
        $response['contents'] = array('Allowed' => array('GET', 'PATCH', 'OPTIONS'));
        $response['code'] = 200;
        break;
    default:
        header('Allow: GET, PATCH, OPTIONS');
        $response['contents'] = 'Not allowed';
        $response['code'] = 405;
        break;
}

echo json_encode($response['contents'], JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
return http_response_code($response['code']);
