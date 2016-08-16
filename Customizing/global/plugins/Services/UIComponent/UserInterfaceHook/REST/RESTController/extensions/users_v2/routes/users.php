<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer and T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */
namespace RESTController\extensions\users_v2;


// This allows us to use shorter names instead of full namespace quantifier
// Requires: $app to be \RESTController\RESTController::getInstance();
use \RESTController\libs\RESTAuth as RESTAuth;


// Group implemented routes into common group
$app->group('/v2/users', function () use ($app) {
  $app->post('/test', function () use ($app) {
    $request  = $app->request;

    $headers = array();
    foreach ($_SERVER as $name => $value)
      if (substr($name, 0, 5) == 'HTTP_')
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;

    $app->success(array(
      'body'   => $request->getBody(),                    // XML, JSON
      'post'   => $request->post(),                       // Form
      'get'    => $request->get(),                        // GET
      'slim'   => \Slim\Http\Headers::extract($_SERVER),  // Header
      'man'    => $headers                                // Header
    ));
  });

  /**
   *
   */
  $app->get('/list', RESTAuth::checkAccess(RESTAuth::PERMISSION), function () use ($app) {
    // Return some fields only as admin
    // Respect profile visibility

    $app->halt(501, 'Not yet implemented...');
  });


  /**
   *
   */
  $app->get('/search', RESTAuth::checkAccess(RESTAuth::PERMISSION), function () use ($app) {
    // Return some fields only as admin
    // Respect profile visibility

    $app->halt(501, 'Not yet implemented...');
  });


  /**
   *
   */
  $app->get('/account/:id', RESTAuth::checkAccess(RESTAuth::PERMISSION), function ($id) use ($app) {
    // Return some fields only as admin
    // Respect profile visibility

    $app->halt(501, 'Not yet implemented...');
  });


  /**
   *
   */
  $app->put('/account', RESTAuth::checkAccess(RESTAuth::ADMIN), function () use ($app) {
    // Edit only own account
    // Admins may edit others

    $app->halt(501, 'Not yet implemented...');
  });
// End of URI group
});
