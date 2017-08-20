<?php

namespace Apps\Subscribers;

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
        $subscribers = new \Apps\Subscribers\GetSubscribers();
        $subscriberID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
        $response = $subscribers->get($subscriberID);
        break;
    case 'POST':
        if ($isValid) {
            $subscribers = new \Apps\Subscribers\AddSubscribers();
            $response = $subscribers->add($json);
        }
        break;
    case 'PATCH':
        if ($isValid) {
            $subscribers = new \Apps\Subscribers\PatchSubscribers();
            $subscriberID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $subscribers->patch($subscriberID, $json);
        }
        break;
    case 'DELETE':
        if ($isValid) {
            $subscribers = new \Apps\Subscribers\DeleteSubscribers();
            $subscriberID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $subscribers->delete($subscriberID);
        }
        break;
    case 'OPTIONS':
        header('Allow: GET, POST, PATCH, DELETE, OPTIONS');
        $response['contents'] = array('Allowed' => array('GET', 'POST', 'PATCH', 'DELETE', 'OPTIONS'));
        $response['code'] = 200;
        break;
    default:
        header('Allow: GET, POST, PATCH, DELETE; OPTIONS');
        $response['contents'] = 'Not allowed';
        $response['code'] = 405;
        break;
}

echo json_encode($response['contents'], JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
return http_response_code($response['code']);
