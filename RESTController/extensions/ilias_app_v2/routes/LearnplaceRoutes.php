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


	//only numeric values are valid
	$condition = [
		'objectId' => '[0-9]+'
	];

	//set json content type for all sub routes
	$app->response()->headers()->set('Content-Type', 'application/json');

	$app->get('/:objectId', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app) {
		try {
			$learnplacePlugin = new LearnplacePlugin();
			$responseContent = json_encode($learnplacePlugin->fetchByObjectId($objectId));
			$app->response()->body($responseContent);
		}
		catch (\Exception $exception) {
			$responseContent = json_encode(new ErrorAnswer($exception->getMessage()));
			$app->response()->body($responseContent);
		}

	})->conditions($condition);

	$app->options('/:objectId', function ($objectId) {})->conditions($condition);

	$app->get('/:objectId/journal-entries', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app) {
		$app->response()->body('{"not": "implemented"}');
	})->conditions($condition);

	$app->options('/:objectId/journal-entries', function ($objectId) {})->conditions($condition);

	$app->post('/:objectId/journal-entry', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app) {
		$app->response()->body('{"not": "implemented"}');
	})->conditions($condition);

	$app->options('/:objectId/journal-entry', function ($objectId) {})->conditions($condition);

	$app->get('/:objectId/blocks', RESTAuth::checkAccess(RESTAuth::TOKEN), function($objectId) use ($app) {
		$app->response()->body('{"not": "implemented"}');
	})->conditions($condition);

	$app->options('/:objectId/blocks', function ($objectId) {})->conditions($condition);
});