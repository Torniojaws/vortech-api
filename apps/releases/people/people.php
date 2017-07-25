<?php

namespace Apps\Releases\People;

require_once(__DIR__.'/../../../autoloader.php');
spl_autoload_register('VortechAPI\Autoloader\Loader::load');

header('Content-Type: application/json');

// Check the request
$request = new \Apps\Utils\Request($_SERVER, $_GET);
$json = file_get_contents('php://input');
$hasValidJSON = $request->hasValidJSON($json);
$hasValidID = $request->hasValidID();

// Build an error response if a valid JSON is required and it is not valid
if ($request->isMissingRequiredJSON($json)) {
    $response = $request->getInvalidJSONResponse();
}

// Build missing ID response. If none is needed, $response will be overwritten by the valid case
if ($hasValidID == false) {
    $response = $request->getInvalidIDResponse();
}

switch ($request->getMethod()) {
    case 'GET':
        if ($hasValidID) {
            $people = new \Apps\Releases\People\GetPeople();
            $releaseID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $people->get($releaseID);
        }
        break;
    case 'PATCH':
        if ($hasValidID && $hasValidJSON) {
            $people = new \Apps\Releases\People\PatchPeople();
            $releaseID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $people->patch($releaseID, $json);
        }
        break;
    default:
        header('Allow: GET, PATCH');
        $response['contents'] = 'Unknown or unimplemented HTTP Method';
        $response['code'] = 405;
        break;
}

echo json_encode($response['contents'], JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
return http_response_code($response['code']);
