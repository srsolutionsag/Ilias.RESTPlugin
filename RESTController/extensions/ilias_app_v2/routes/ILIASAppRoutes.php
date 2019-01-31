<?php namespace RESTController\extensions\ILIASApp\V2;

require_once(dirname(__DIR__) . '/models/ILIASAppModel.php');
require_once('./Services/Membership/classes/class.ilParticipants.php');

use \RESTController\libs\RESTAuth as RESTAuth;
use RESTController\RESTController;

/** @var $app RESTController */
/**
 * Note: The additional OPTIONS request per route is needed due to CORS. Before sending an actual GET/POST request,
 * the browser is sending an OPTIONS request to check if the origin (e.g. localhost) is allowed to perform
 * cross origin site requests. The OPTIONS request is sent without Authorization headers und thus results in a 401 if
 * the TOKEN middleware is active.
 */
$app->group('/v2/ilias-app', function () use ($app) {

	$app->get('/desktop', RESTAuth::checkAccess(RESTAuth::TOKEN), function() use ($app) {
		$iliasApp = new ILIASAppModel();
		$accessToken = $app->request->getToken();
		$userId = $accessToken->getUserId();
		$app->response->headers->set('Content-Type', 'application/json');
		$app->response->body(json_encode($iliasApp->getDesktopData($userId)));
	});

	$app->get('/objects/:refId', RESTAuth::checkAccess(RESTAuth::TOKEN), function($refId) use ($app) {
		$iliasApp = new ILIASAppModel();
		$accessToken = $app->request->getToken();
		$userId = $accessToken->getUserId();
		$app->response->headers->set('Content-Type', 'application/json');
		$recursive = $app->request->get('recursive');
		$data = ($recursive) ? $iliasApp->getChildrenRecursive($refId, $userId) : $iliasApp->getChildren($refId, $userId);
		$app->response->body(json_encode($data));
	});

	$app->get('/objectsfromlist/:refIds', RESTAuth::checkAccess(RESTAuth::TOKEN), function($refIds) use ($app) {
		$iliasApp = new ILIASAppModel();
		$app->response->headers->set('Content-Type', 'application/json');
		$data = $iliasApp->getChildrenRecursiveFromRefIdList(explode(",",$refIds));
		$app->response->body(json_encode($data));
	});

	$app->get('/files/:refId', RESTAuth::checkAccess(RESTAuth::TOKEN), function($refId) use ($app) {
		$iliasApp = new ILIASAppModel();
		$accessToken = $app->request->getToken();

		$userId = $accessToken->getUserId();
		$app->response->headers->set('Content-Type', 'application/json');

		//ensure type safety
		$fileData = $iliasApp->getFileData($refId, $userId);
		$fileData["fileVersion"] = strval($fileData["fileVersion"]);
		$fileData["fileSize"] = strval($fileData["fileSize"]);

		$app->response->body(json_encode($fileData));
	});

	/**
	 * Returns a very short live token to log in via the ILIAS Pegasus Helper plugin.
	 */
	$app->get('/auth-token', RESTAuth::checkAccess(RESTAuth::TOKEN), function() use ($app) {
		$iliasApp = new ILIASAppModel();
		$accessToken = $app->request->getToken();
		$userId = $accessToken->getUserId();
		$token = $iliasApp->createToken($userId);
		$app->response->body(json_encode("{\"token\":\"$token\"}"));
	});

	//add learnplace routes
	require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/routes/LearnplaceRoutes.php';

	//add news routes
	require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/routes/NewsRoutes.php';
});
