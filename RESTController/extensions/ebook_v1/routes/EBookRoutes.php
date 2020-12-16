<?php

namespace RESTController\extensions\eBook;

use RESTController\libs\RESTAuth as RESTAuth;
use RESTController\RESTController;
use ilCachedPlugin;
use ilPluginAdmin;

// Deactivate routes if ebook plugin is not active
if (!ilPluginAdmin::isPluginActive('xebk')) {
    return;
}

/** @var $app RESTController */
/**
 * Note: The additional OPTIONS request per route is needed due to CORS. Before sending an actual GET/POST request,
 * the browser is sending an OPTIONS request to check if the origin (e.g. localhost) is allowed to perform
 * cross origin site requests. The OPTIONS request is sent without Authorization headers und thus results in a 401 if
 * the TOKEN middleware is active.
 */
$app->group('/v1/ebook', function () use ($app) {

	/**
	 * GET all files
	 */
	$app->get('/', RESTAuth::checkAccess(RESTAuth::TOKEN), function() use ($app) {
		$accessToken = $app->request()->getToken();
		$model = new EBookModel();
		$userId = $accessToken->getUserId();
		$app->response->body(json_encode($model->getEBooks($userId)));
	});

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

	/**
	 * GET key
	 */
	$app->post('/:refId/key', RESTAuth::checkAccess(RESTAuth::TOKEN), function($ref_id) use ($app) {
		$accessToken = $app->request()->getToken();
		$model = new EBookModel();
		$userId = $accessToken->getUserId();

		try {
			$key = $model->getKeyByRefId($userId, $ref_id);
			$remote_address = $_SERVER['REMOTE_ADDR'];
			$forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'];
			$hardware_id = $app->request()->getParameter('hardware_id');
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

	/**
	 * GET key
	 */
	$app->post('/log-error', RESTAuth::checkAccess(RESTAuth::TOKEN), function() use ($app) {
		$model = new EBookModel(false);
		$headers = getallheaders();
		try {
			$model->errorLog(
				urldecode($headers['message']),
				urldecode($headers['stack-trace']),
				urldecode($headers['version']),
				urldecode($headers['os'])
			);

			$app->response->body(json_encode(["success" => true]));
		} catch (NoFileException $e) {
			$app->halt(404, "No file uploaded yet.");
		} catch (NoAccessException $e) {
			$app->halt(401, "No access.");
		}

	});

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
});
