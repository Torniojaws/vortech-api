<?php

namespace Apps\News;

require_once(__DIR__.'/../../autoloader.php');
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

$newshandler = new \Apps\News\NewsHandler();

switch ($request->getMethod()) {
    case 'GET':
        $news = new \Apps\News\GetNews();
        $newsID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
        $response = $news->get($newsID);
        break;
    case 'POST':
        if ($hasValidJSON) {
            $news = new \Apps\News\AddNews();
            $response = $news->add($json);
        }
        break;
    case 'PUT':
        if ($hasValidJSON && $hasValidID) {
            $news = new \Apps\News\EditNews();
            $newsID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $news->update($newsID, $json);
        }
        break;
    case 'DELETE':
        if ($hasValidID) {
            $news = new \Apps\News\DeleteNews();
            $newsID = isset($request->getParams()[1]) ? $request->getParams()[1] : null;
            $response = $news->delete($newsID);
        }
        break;
    default:
        header('Allow: GET, POST, PUT, DELETE');
        $response['contents'] = 'Unknown or unimplemented HTTP Method';
        $response['code'] = 405;
        break;
}

echo json_encode($response['contents'], JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES);
return http_response_code($response['code']);
