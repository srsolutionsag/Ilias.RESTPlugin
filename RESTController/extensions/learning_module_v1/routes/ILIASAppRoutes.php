<?php namespace RESTController\extensions\ILIASApp\V1;

use RESTController\libs\RESTAuth as RESTAuth;
use RESTController\RESTController;

/** @var $app RESTController */
/**
 * Note: The additional OPTIONS request per route is needed due to CORS. Before sending an actual GET/POST request,
 * the browser is sending an OPTIONS request to check if the origin (e.g. localhost) is allowed to perform
 * cross origin site requests. The OPTIONS request is sent without Authorization headers und thus results in a 401 if
 * the TOKEN middleware is active.
 */
$app->group('/v1/learning-module', function () use ($app) {

    $app->get('/:refId', RESTAuth::checkAccess(RESTAuth::TOKEN), function($refId) use ($app) {
        $iliasApp = new ILIASAppModel();

        $response = $iliasApp->getLearningModuleData($refId);

        $app->response->headers->set('Content-Type', 'application/json');
        $app->response()->body(json_encode($response["body"]));
        if(isset($response["status"]))
            $app->response()->status($response["status"]);
    });
});
