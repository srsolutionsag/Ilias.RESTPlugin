<?php

use RESTController\libs\RESTAuth;
use RESTController\libs\RESTilias;
use RESTController\libs\RESTLib;
use RESTController\RESTController;
use SRAG\Plugin\eBook\Container\EBookPluginContainer;
use SRAG\Plugin\eBook\Security\Exception\AccessViolationException;
use SRAG\Plugin\eBook\Security\Exception\SecurityException;
use SRAG\Plugin\eBook\Synchronization\Service\SynchronizationManager;
use SRAG\Plugin\eBook\Synchronization\Service\SynchronizationMapper;

/** @var $app RESTController */

$app->group('/v2/ebook', function () use ($app) {

	$app->post('/sync', RESTAuth::checkAccess(RESTAuth::TOKEN), function() use ($app) {

		try {
			$accessToken = $app->request()->getToken();
			$userId = $accessToken->getUserId();

			RESTilias::initAccessHandling();

			/**
			 * @var  array $body
			 */
			$body = $app->request()->getBody();

			$mapper = new SynchronizationMapper();
			$model = $mapper->fromJson($body);
			$synchronization = $mapper->fromModel($model, $userId);

			/**
			 * @var SynchronizationManager $synchManager
			 */
			$synchManager = EBookPluginContainer::resolve(SynchronizationManager::class);
			$finishedSync = $synchManager->synchronize($synchronization);
			$model = $mapper->toModel($finishedSync);

			if(json_last_error() === JSON_ERROR_NONE) {
				$app->response()->setBody(json_encode($model));
				$app->response()->setStatus(200);
				return;
			}
		}
		catch (AccessViolationException $exception) {
			require_once __DIR__ . '/../models/ErrorMessage.php';
			$app->response()->setBody(json_encode(new ErrorMessage('Access violation one or more books are not accessible by the user.')));
			$app->response()->setStatus(403);
			return;
		}

		$app->response()->setBody('Server was unable to calculate the sync response.');
		$app->response()->setStatus(500);
	});
});