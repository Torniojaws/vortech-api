<?php

namespace Apps\Contacts;

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
        $contacts = new \Apps\Contacts\GetContacts();
        $contactID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
        $response = $contacts->get($contactID);
        break;
    case 'POST':
        if ($isValid) {
            $contacts = new \Apps\Contacts\AddContacts();
            $response = $contacts->add($json);
        }
        break;
    case 'PATCH':
        if ($isValid) {
            $contacts = new \Apps\Contacts\PatchContacts();
            $contactID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $contacts->edit($contactID, $json);
        }
        break;
    case 'OPTIONS':
        header('Allow: GET, POST, PATCH, OPTIONS');
        $response['contents'] = array('Allowed' => array('GET', 'POST', 'PATCH', 'OPTIONS'));
        $response['code'] = 200;
        break;
    default:
        header('Allow: GET, POST, PATCH, OPTIONS');
        $response['contents'] = 'Not allowed';
        $response['code'] = 405;
        break;
}

echo json_encode($response['contents'], JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
return http_response_code($response['code']);
