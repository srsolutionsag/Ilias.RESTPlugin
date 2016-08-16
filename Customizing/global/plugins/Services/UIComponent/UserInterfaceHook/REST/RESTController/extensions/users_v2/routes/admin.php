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
  /**
   *
   */
  $app->post('/account', RESTAuth::checkAccess(RESTAuth::ADMIN), function () use ($app) {
    $app->halt(501, 'Not yet implemented...');
  });


  /**
   *
   */
  $app->delete('/account', RESTAuth::checkAccess(RESTAuth::ADMIN), function () use ($app) {
    $app->halt(501, 'Not yet implemented...');
  });
// End of URI group
});
