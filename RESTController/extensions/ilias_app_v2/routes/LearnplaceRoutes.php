<?php

use RESTController\extensions\ILIASApp\V2\data\ErrorAnswer;
use RESTController\extensions\ILIASApp\V2\LearnplacePlugin;
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

	//bootstrap learnplaces plugin
	require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/Learnplaces/classes/bootstrap.php';
	require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/LearnplacePlugin.php';
	require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/ErrorAnswer.php';
	require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/BlockCollection.php';


	//only numeric values are valid
	$condition = [
		'objectId' => '[0-9]+'
	];

	//set json content type for all sub routes
	$app->response()->headers()->set('Content-Type', 'application/json');

	$app->error(function($message) use ($app) {
		print json_encode(new ErrorAnswer($message));
	});


	$app->get('/:objectId', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app) {
		try {
			$learnplacePlugin = new LearnplacePlugin();
			$responseContent = json_encode($learnplacePlugin->fetchByObjectId($objectId));
			$app->response()->body($responseContent);
		}
		catch (\Exception $exception) {
			$app->error($exception->getMessage());
		}

	})->conditions($condition);

	$app->options('/:objectId', function ($objectId) {})->conditions($condition);

	$app->get('/:objectId/journal-entries', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app) {
		try {
			$learnplacePlugin = new LearnplacePlugin();
			$responseContent = json_encode($learnplacePlugin->fetchVisitJournal($objectId));
			$app->response()->body($responseContent);
		}
		catch (\Exception $exception) {
			$app->error($exception->getMessage());
		}
	})->conditions($condition);

	$app->options('/:objectId/journal-entries', function ($objectId) {})->conditions($condition);

	$app->post('/:objectId/journal-entry', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app) {

		try {
			$learnplacePlugin = new LearnplacePlugin();

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
		catch (\Exception $exception) {
			$app->error($exception->getMessage());
		}

	})->conditions($condition);

	$app->options('/:objectId/journal-entry', function ($objectId) {})->conditions($condition);

	$app->get('/:objectId/blocks', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app) {
		try {
			$learnplacePlugin = new LearnplacePlugin();
			$blocks = $learnplacePlugin->fetchBlocks($objectId);
			$app->response()->body($blocks);
		}
		catch (\Exception $exception) {
			$app->error($exception->getMessage());
		}

	})->conditions($condition);

	$app->options('/:objectId/blocks', function ($objectId) {})->conditions($condition);
});