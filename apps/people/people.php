<?php

namespace Apps\People;

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
        $people = new \Apps\People\GetPeople();
        $personID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
        $response = $people->get($personID);
        break;
    case 'POST':
        if ($isValid) {
            $people = new \Apps\People\AddPeople();
            $response = $people->add($json);
        }
        break;
    case 'PUT':
        if ($isValid) {
            $people = new \Apps\People\EditPeople();
            $personID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $people->edit($personID, $json);
        }
        break;
    case 'PATCH':
        if ($isValid) {
            $people = new \Apps\People\PatchPeople();
            $personID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $people->patch($personID, $json);
        }
        break;
    case 'OPTIONS':
        header('Allow: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response['contents'] = array('Allowed' => array('GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'));
        $response['code'] = 200;
        break;
    case 'DELETE':
        if ($isValid) {
            $people = new \Apps\People\DeletePeople();
            $personID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $release->delete($personID);
        }
        break;
    default:
        header('Allow: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response['contents'] = 'Not allowed';
        $response['code'] = 405;
        break;
}

echo json_encode($response['contents'], JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
return http_response_code($response['code']);
