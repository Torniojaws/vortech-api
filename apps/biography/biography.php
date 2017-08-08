<?php

namespace Apps\Biography;

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
        $bio = new \Apps\Biography\GetBiography();
        $response = $bio->get();
        break;
    case 'POST':
        if ($isValid) {
            $bio = new \Apps\Biography\AddBiography();
            $response = $bio->add($json);
        }
        break;
    case 'PUT':
        if ($isValid) {
            $bio = new \Apps\Biography\EditBiography();
            $response = $bio->edit($json);
        }
        break;
    case 'PATCH':
        if ($isValid) {
            $bio = new \Apps\Biography\PatchBiography();
            $response = $bio->patch($json);
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
