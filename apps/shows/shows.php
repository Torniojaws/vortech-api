<?php

namespace Apps\Shows;

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
        $shows = new \Apps\Shows\GetShows();
        $showID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
        $response = $shows->get($showID);
        break;
    case 'POST':
        if ($isValid) {
            $shows = new \Apps\Shows\AddShow();
            $response = $shows->add($json);
        }
        break;
    case 'PUT':
        if ($isValid) {
            $shows = new \Apps\Shows\EditShow();
            $showID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $shows->edit($showID, $json);
        }
        break;
    case 'PATCH':
        if ($isValid) {
            $shows = new \Apps\Shows\PatchShow();
            $showID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $shows->patch($showID, $json);
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
