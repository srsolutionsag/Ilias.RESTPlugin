<?php

use RESTController\database\RESTaccess;
use RESTController\extensions\ILIASApp\V2\data\ErrorAnswer;
use RESTController\extensions\ILIASApp\V2\FileHashProviderFactory;
use RESTController\extensions\ILIASApp\V2\LearnplacePlugin;
use RESTController\libs\Exceptions\RBAC;
use RESTController\libs\RESTAuth;
use RESTController\RESTController;

/** @var $app RESTController */
/**
 * File LearnplaceRoutes.php
 *
 * This file contains all learnplace related routes.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
$app->group('/learnplace', function() use ($app) {

	$init = function(RESTController $app) {
		require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/LearnplacePlugin.php';
		require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/ErrorAnswer.php';
		require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/BlockCollection.php';
		require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/services/FileHashing/FileHashProvider.php';
		require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/services/FileHashing/FileHashProviderFactory.php';

		//set json content type for all sub routes
		$app->response()->headers()->set('Content-Type', 'application/json');

		$app->error(function($message) use ($app) {
			print json_encode(new ErrorAnswer($message));
		});

		$plugins = new CallbackFilterIterator(new RecursiveIteratorIterator(new RecursiveArrayIterator(ilPluginAdmin::$active_plugins)), function($current, $key, $iterator) {
			return is_string($current) && strcmp($current, 'Learnplaces') === 0;
		});

		if(iterator_count($plugins) === 0) {
			throw new \Exception("No learnplace plugin found or activated!");
		}

		//bootstrap learnplaces plugin
		require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/Learnplaces/classes/bootstrap.php';
	};

	//only numeric values without leading zeros are valid
	$condition = [
		'objectId' => '(?!0)[0-9]+'
	];


	$app->get('/:objectId', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app, $init) {
		try {
			$init($app);
			$learnplacePlugin = createLearnplacePlugin();
			$responseContent = json_encode($learnplacePlugin->fetchByObjectId($objectId));
			$app->response()->body($responseContent);
		}
		catch (RBAC $exception) {
			$app->response()->body(json_encode(new ErrorAnswer($exception->getMessage())));
			$app->response()->status(RBAC::STATUS);
		}
		catch (\Exception $exception) {
			$app->error($exception->getMessage());
		}

	})->conditions($condition);

	$app->options('/:objectId', function ($objectId) use ($app) {
		$app->response->headers->set('Access-Control-Max-Age', '600');
	})->conditions($condition);

	$app->get('/:objectId/journal-entries', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app, $init) {
		try {
			$init($app);
			$learnplacePlugin = createLearnplacePlugin();
			$responseContent = json_encode($learnplacePlugin->fetchVisitJournal($objectId));
			$app->response()->body($responseContent);
		}
		catch (RBAC $exception) {
			$app->response()->body(json_encode(new ErrorAnswer($exception->getMessage())));
			$app->response()->status(RBAC::STATUS);
		}
		catch (\Exception $exception) {
			$app->error($exception->getMessage());
		}
	})->conditions($condition);

	$app->options('/:objectId/journal-entries', function ($objectId) use ($app) {
		$app->response->headers->set('Access-Control-Max-Age', '600');
	})->conditions($condition);

	$app->post('/:objectId/journal-entry', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app, $init) {
		try {
			$init($app);
			$learnplacePlugin = createLearnplacePlugin();

			/**
			 * @var array $json
			 */
			$json = $app->request()->getBody();
			$start2017 = 1483228800;
			if(
				is_array($json) === true &&
				array_key_exists('time', $json) === true &&
				intval($json['time']) <= time() &&
				intval($json['time']) > $start2017
			) {
				$learnplacePlugin->visitLearnplace($objectId, intval($json['time']));
				$app->response()->body('{"message": "Visit journal entry was successful created"}');
			}
			else {
				$unprocessableEntity = 422;
				$app->response()->body(json_encode(new ErrorAnswer('Invalid post data.')));
				$app->response()->status($unprocessableEntity);
			}
		}
		catch (RBAC $exception) {
			$app->response()->body(json_encode(new ErrorAnswer($exception->getMessage())));
			$app->response()->status(RBAC::STATUS);
		}
		catch (\Exception $exception) {
			$app->error($exception->getMessage());
		}

	})->conditions($condition);

	$app->options('/:objectId/journal-entry', function ($objectId) use ($app) {
		$app->response->headers->set('Access-Control-Max-Age', '600');
	})->conditions($condition);

	$app->get('/:objectId/blocks', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app, $init) {
		try {
			$init($app);
			$learnplacePlugin = createLearnplacePlugin();
			$blocks = $learnplacePlugin->fetchBlocks($objectId);
			$app->response()->body($blocks);
		}
		catch (RBAC $exception) {
			$app->response()->body(json_encode(new ErrorAnswer($exception->getMessage())));
			$app->response()->status(RBAC::STATUS);
		}
		catch (\Exception $exception) {
			$app->error($exception->getMessage());
		}

	})->conditions($condition);

	$app->options('/:objectId/blocks', function ($objectId) use ($app) {
		$app->response->headers->set('Access-Control-Max-Age', '600');
	})->conditions($condition);

	/**
	 * Factory function for the learnplace plugin.
	 *
	 * @return LearnplacePlugin $learnplace
	 */
	function createLearnplacePlugin() {
		return new LearnplacePlugin((new FileHashProviderFactory())->create());
	}
});

