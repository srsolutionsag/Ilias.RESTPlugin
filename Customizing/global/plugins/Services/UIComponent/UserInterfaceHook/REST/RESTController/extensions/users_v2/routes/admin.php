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
use \RESTController\lib           as Libs;
use \RESTController\libs\RESTAuth as RESTAuth;


// Group implemented routes into common group
$app->group('/v2/users', function () use ($app) {
  /**
   *
   */
  $app->put('/account', RESTAuth::checkAccess(RESTAuth::PERMISSION), function () use ($app) {
    // Edit only own account
    // Admins may edit others

    $app->halt(501, 'Not yet implemented...');
  });


  /**
   *
   */
  $app->post('/account', RESTAuth::checkAccess(RESTAuth::PERMISSION), function () use ($app) {
    // Catch and handle exceptions if possible
    try {
      // Fetch input parameters
      $request      = $app->request;
      $refId        = $request->getParameter('ref_id', USER_FOLDER_ID);
      $token        = $request>getToken();
      $adminUserId  = $token->getUserId();

      // Initialize RBAC (user is fetched from access-token)
      Libs\RESTIlias::loadIlUser();
      global $ilUser, $ilSetting, $ilAccess, $rbacsystem, $rbacadmin, $rbacreview;
      
      //
      $result = UserAdmin::CreateUser($adminUserId, $refId);

      //
      $app->success('Implementing...');
    }
    // Catch missing input parameters
    catch (Libs\Exceptions\MissingParameter $e) {
      $app->halt(400, $e->getFormatedMessage(), $e->getRESTCode());
    }
  });


  /**
   *
   */
  $app->delete('/account', RESTAuth::checkAccess(RESTAuth::PERMISSION), function () use ($app) {
    $app->halt(501, 'Not yet implemented...');
  });
// End of URI group
});
