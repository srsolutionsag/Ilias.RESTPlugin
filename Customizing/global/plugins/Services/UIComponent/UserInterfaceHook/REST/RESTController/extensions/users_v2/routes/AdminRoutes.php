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
  $app->get('/test', RESTAuth::checkAccess(RESTAuth::PERMISSION), function () use ($app) {
    include_once './Services/AccessControl/classes/class.ilObjRole.php';


    // Initialize RBAC (user is fetched from access-token)
    Libs\RESTilias::loadIlUser();
    global $ilUser, $ilSetting, $ilAccess, $rbacsystem, $rbacadmin, $rbacreview, $lng;

    // var_dump(AdminModel::ValidateRoles(7, array(56)));
    //var_dump(AdminModel::GetAllowedRoles(61));

    //var_dump($rbacreview->getAssignableRolesInSubtree(61));
    //var_dump($rbacreview->getAssignableRoles());

    $refId       = 7;

    $local  = $rbacreview->getRolesOfRoleFolder($refId);
    $global = $rbacreview->getGlobalRoles();
    if ($refId != USER_FOLDER_ID)
      $global = array_filter($global, function($role) {
        return \ilObjRole::_getAssignUsersStatus($role);
      });
    $assignable = array_merge($local, $global);
    $assignable = array_map('intval', $assignable);


    //
    //var_dump($assignable);
    //var_dump($assignable);
    //var_dump($rbacreview->getAssignableRolesInSubtree(61));
    include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
    var_dump(\ilAuthUtils::_getActiveAuthModes());

    //var_dump($rbacreview->getGlobalAssignableRoles());

    //var_dump($rbacreview->getUserPermissionsOnObject($ilUser->getId(), 61));



    die;
  });


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
      Libs\RESTilias::loadIlUser();
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
