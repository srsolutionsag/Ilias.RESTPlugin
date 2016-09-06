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
use \RESTController\libs          as Libs;
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
      $request  = $app->request;
      $refId    = $request->getParameter('ref_id', AdminModel::USER_FOLDER_ID);
      $userData = array();
      foreach (AdminModel::fields as $field) {
        $value = $request->getParameter($field);
        if (isset($value))
          $userData[$field] = $value;
      }

      // Initialize RBAC (user is fetched from access-token)
      Libs\RESTilias::loadIlUser();
      Libs\RESTilias::initAccessHandling();

      // Check input, create/update user and assign roles
      $cleanUserData = AdminModel::CheckUserData($userData, AdminModel::MODE_CREATE, $refId);
      $result        = AdminModel::StoreUserData($cleanUserData, AdminModel::MODE_CREATE, $refId);

      // Return updated user data
      $app->success($cleanUserData);
    }
    // Catch any exception
    catch (Libs\LibException $e) {
      $app->halt(500, $e->getFormatedMessage(), $e->getRESTCode());
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
