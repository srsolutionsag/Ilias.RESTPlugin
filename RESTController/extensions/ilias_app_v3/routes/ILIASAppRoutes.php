<?php namespace RESTController\extensions\ILIASApp\V3;

use RESTController\libs\RESTAuth as RESTAuth;
use RESTController\RESTController;

/** @var $app RESTController */
/**
 * Note: The additional OPTIONS request per route is needed due to CORS. Before sending an actual GET/POST request,
 * the browser is sending an OPTIONS request to check if the origin (e.g. localhost) is allowed to perform
 * cross origin site requests. The OPTIONS request is sent without Authorization headers und thus results in a 401 if
 * the TOKEN middleware is active.
 */
$app->group('/v3/ilias-app', function () use ($app) {

	$app->get('/files/:refId', RESTAuth::checkAccess(RESTAuth::TOKEN), function($refId) use ($app) {
		$iliasApp = new ILIASAppModel();
		$accessToken = $app->request->getToken();

		$userId = $accessToken->getUserId();
        $response = $iliasApp->getFileData($refId, $userId);

        $app->response->headers->set('Content-Type', 'application/json');
        $app->response()->body(json_encode($response["body"]));
        if(isset($response["status"]))
            $app->response()->status($response["status"]);
	});

	$app->post('/files/:refId/learning-progress-to-done', RESTAuth::checkAccess(RESTAuth::TOKEN), function($refId) use ($app) {
		$iliasApp = new ILIASAppModel();
		$accessToken = $app->request->getToken();

		$userId = $accessToken->getUserId();
        $response = $iliasApp->setFileLearningProgressToDone($refId, $userId);

        $app->response->headers->set('Content-Type', 'application/json');
        $app->response()->body(json_encode($response["body"]));
        if(isset($response["status"]))
            $app->response()->status($response["status"]);
	});

});
