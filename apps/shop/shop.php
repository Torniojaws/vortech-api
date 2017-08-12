<?php

namespace Apps\Shop;

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
        $shop = new \Apps\Shop\GetShop();
        $shopID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
        $response = $shop->get($shopID);
        break;
    case 'POST':
        if ($isValid) {
            $shop = new \Apps\Shop\AddShop();
            $response = $shop->add($json);
        }
        break;
    case 'PUT':
        if ($isValid) {
            $shop = new \Apps\Shop\EditShop();
            $shopID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $shop->edit($shopID, $json);
        }
        break;
    case 'PATCH':
        if ($isValid) {
            $shop = new \Apps\Shop\PatchShop();
            $shopID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $shop->patch($shopID, $json);
        }
        break;
    case 'DELETE':
        if ($isValid) {
            $shop = new \Apps\Shop\DeleteShop();
            $shopID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $shop->delete($shopID, $json);
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
