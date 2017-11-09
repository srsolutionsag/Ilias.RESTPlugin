<?php

namespace RESTController\extensions\eBook;

require_once(dirname(__DIR__) . '/models/EBookModel.php');
require_once("./Services/FileDelivery/classes/class.ilFileDelivery.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/eBook/classes/class.ileBookAccessLog.php");


use \RESTController\libs\RESTAuth as RESTAuth;
use \RESTController\core\auth as Auth;
use RESTController\RESTController;

/** @var $app RESTController */
/**
 * Note: The additional OPTIONS request per route is needed due to CORS. Before sending an actual GET/POST request,
 * the browser is sending an OPTIONS request to check if the origin (e.g. localhost) is allowed to perform
 * cross origin site requests. The OPTIONS request is sent without Authorization headers und thus results in a 401 if
 * the TOKEN middleware is active.
 */
$app->group('/v1/ebook', function () use ($app) {

	/**
	 * set CORS Headers
	 */
	$app->response->headers->set('Access-Control-Allow-Origin', '*');
	$app->response->headers->set('Access-Control-Allow-Headers', 'Authorization,XDEBUG_SESSION,XDEBUG_SESSION_START');
	$app->response->headers->set('Access-Control-Allow-Methods', 'GET,POST');

	/**
	 * GET all files
	 */
	$app->get('/', RESTAuth::checkAccess(RESTAuth::TOKEN), function() use ($app) {
		$accessToken = $app->request->getToken();
		$model = new EBookModel();
		$userId = $accessToken->getUserId();
		$app->response->body(json_encode($model->getEBooks($userId)));
	});
	$app->options('/', function() {});

	/**
	 * GET encoded file binary
	 */
	$app->get('/:refId/file', RESTAuth::checkAccess(RESTAuth::TOKEN), function($ref_id) use ($app) {
		$accessToken = $app->request->getToken();
		$model = new EBookModel();
		$userId = $accessToken->getUserId();

		try {
			$filePath = $model->getFilePathByRefId($userId, $ref_id);
		} catch (NoFileException $e) {
			$app->halt(404, "No file uploaded yet.");
		} catch (NoAccessException $e) {
			$app->halt(401, "No access.");
		}
		/**
		 * @var $ilClientIniFile ilIniFile
		 */
		require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');

		$ilFileDelivery = new \ilFileDelivery($filePath);
		$ilFileDelivery->setMimeType('application/pdf');
		$ilFileDelivery->deliver();
	});
	$app->options('/:refId/file', function() {});

	/**
	 * GET key
	 */
	$app->post('/:refId/key', RESTAuth::checkAccess(RESTAuth::TOKEN), function($ref_id) use ($app) {
		$accessToken = $app->request->getToken();
		$model = new EBookModel();
		$userId = $accessToken->getUserId();

		try {
			$key = $model->getKeyByRefId($userId, $ref_id);
			$remote_address = $_SERVER['REMOTE_ADDR'];
			$forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'];
			$hardware_id = $app->request->getParameter('hardware_id');
			$access = new \ileBookAccessLog();
			$access->setUserId($userId);
			$access->setEbookId($ref_id);
			$access->setRemoteAddress($remote_address);
			$access->setXForwardedFor($forwarded_for);
			$access->setHardwareId($hardware_id);
			$access->updateTimestamp();
			$access->setAction(\ileBookAccessLog::ACTION_DOWNLOAD_TOKEN);
			$access->create();
			$access->triggerCheck();

			$app->response->body(json_encode(["key" => $key]));
		} catch (NoFileException $e) {
			$app->halt(404, "No file uploaded yet.");
		} catch (NoAccessException $e) {
			$app->halt(401, "No access.");
		}

	});
	$app->options('/:refId/file', function() {});

	/**
	 * GET if key is still valid
	 *
	 * returns success if the logged in user still has access to the ebook.
	 */
	$app->get('/:refId/validate-key', RESTAuth::checkAccess(RESTAuth::TOKEN), function($ref_id) use ($app) {
		$accessToken = $app->request->getToken();
		$model = new EBookModel();
		$userId = $accessToken->getUserId();

		try {
			$model->getKeyByRefId($userId, $ref_id);
			$app->response->body(json_encode(["success" => true]));
		} catch (NoFileException $e) {
			$app->halt(404, "No file uploaded yet.");
		} catch (NoAccessException $e) {
			$app->halt(401, "No access.");
		}

	});
	$app->options('/:refId/validateKey', function() {});



	//	$app->get('/objects/:refId', RESTAuth::checkAccess(RESTAuth::TOKEN), function($refId) use ($app) {
//		$iliasApp = new ILIASAppModel();
//		$accessToken = $app->request->getToken();
//		$userId = $accessToken->getUserId();
//		$app->response->headers->set('Content-Type', 'application/json');
//		$recursive = $app->request->get('recursive');
//		$data = ($recursive) ? $iliasApp->getChildrenRecursive($refId, $userId) : $iliasApp->getChildren($refId, $userId);
//		$app->response->body(json_encode($data));
//	});
//
//	$app->options('/objects/:refId', function() {});
//
//	$app->get('/files/:refId', RESTAuth::checkAccess(RESTAuth::TOKEN), function($refId) use ($app) {
//		$iliasApp = new ILIASAppModel();
//		$accessToken = $app->request->getToken();
//		$userId = $accessToken->getUserId();
//		$app->response->headers->set('Content-Type', 'application/json');
//		$app->response->body(json_encode($iliasApp->getFileData($refId, $userId)));
//	});
//
//	$app->options('/files/:refId', function() {});

});
