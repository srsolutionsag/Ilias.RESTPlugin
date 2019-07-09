<?php
/**
 * File EBookRoutes.php
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @version 1.0.0
 * @since   EDIT_SINCE
 */

use RESTController\extensions\eBook\v2\models\ErrorMessage;
use RESTController\extensions\eBook\v3\services\JsonSchemaValidation;
use RESTController\libs\RESTAuth;
use RESTController\libs\RESTilias;
use RESTController\RESTController;
use SRAG\Plugin\eBook\Container\EBookPluginContainer;
use SRAG\Plugin\eBook\Security\Exception\AccessViolationException;
use SRAG\Plugin\eBook\Security\Exception\MutexOperationException;
use SRAG\Plugin\eBook\Security\Service\CollisionDetection\UserRequestMutex;
use SRAG\Plugin\eBook\Synchronization\Service\SynchronizationManager;
use SRAG\Plugin\eBook\Synchronization\Service\Mapper\v2\SynchronizationMapper;

/** @var $app RESTController */

$app->group('/v3/ebook', function () use ($app) {
	$app->post('/sync', RESTAuth::checkAccess(RESTAuth::TOKEN), function() use ($app) {

		/**
		 * @var UserRequestMutex $mutex
		 */
		$mutex = NULL;
		$doubleAcquired = false;

		try {

			require_once __DIR__ . '/../services/JsonSchemaValidation.php';

			$accessToken = $app->request()->getToken();
			$userId = $accessToken->getUserId();

			$env = $app->environment();
			JsonSchemaValidation::validateSyncRequest(json_decode($env['slim.input_original']));

			RESTilias::loadIlUser();
			RESTilias::initAccessHandling();

			$mutex = EBookPluginContainer::resolve(UserRequestMutex::class);
			$mutex->acquire();

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
			require_once __DIR__ . '/../../ebook_v2/models/ErrorMessage.php';
			$app->response()->setBody(json_encode(new ErrorMessage('Access violation one or more books are not accessible by the user.')));
			$app->response()->setStatus(403);
			return;
		}
		catch (\Swaggest\JsonSchema\InvalidValue $exception) {
			require_once __DIR__ . '/../../ebook_v2/models/ErrorMessage.php';
			$app->response()->setBody(json_encode(new ErrorMessage($exception->getMessage())));
			$app->response()->setStatus(422);
			return;
		}
		catch (\Swaggest\JsonSchema\Exception $exception) {
			require_once __DIR__ . '/../../ebook_v2/models/ErrorMessage.php';
			$app->response()->setBody(json_encode(new ErrorMessage("Request parsing failed, the server was not able to understand the request.")));
			$app->response()->setStatus(400);
			return;
		}
		catch (MutexOperationException $exception) {
			require_once __DIR__ . '/../../ebook_v2/models/ErrorMessage.php';
			$app->response()->setBody(json_encode(new ErrorMessage("The user already syncs data from another client please try again later.")));
			$app->response()->setStatus(423);
			$doubleAcquired = true;
			return;
		}
		finally {
			if ($mutex !== NULL && $doubleAcquired === false) {
				$mutex->release();
			}
		}

		$app->response()->setBody('Server was unable to calculate the sync response.');
		$app->response()->setStatus(500);
	});
});