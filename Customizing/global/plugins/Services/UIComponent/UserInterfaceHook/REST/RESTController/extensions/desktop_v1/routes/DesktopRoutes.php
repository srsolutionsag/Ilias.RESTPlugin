<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer, T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */
namespace RESTController\extensions\desktop_v1;

// This allows us to use shortcuts instead of full quantifier
use \RESTController\libs as Libs;
use \RESTController\core\auth as Auth;
use \RESTController\libs\Exceptions as LibExceptions;

$app->group('/v1', function () use ($app) {

    /**
     * Retrieves all items from the personal desktop of the authenticated user.
     */
    $app->get('/desktop/overview', '\RESTController\libs\OAuth2Middleware::TokenRouteAuth' , function () use ($app) {
        $auth = new Auth\Util();
        $accessToken = $auth->getAccessToken();
        $authorizedUserId = $accessToken->getUserId();

        $model = new DesktopModel();
        $data = $model->getPersonalDesktopItems($authorizedUserId);

        $app->success($data);
    });


    /**
     * Deletes an item specified by ref_id from the personal desktop of the user specified by $id.
     */
    $app->delete('/desktop/overview', '\RESTController\libs\OAuth2Middleware::TokenRouteAuth',  function () use ($app) {
        $auth = new Auth\Util();
        $accessToken = $auth->getAccessToken();
        $authorizedUserId = $accessToken->getUserId();
        $request = $app->request();
        try {
            $ref_id = $request->params("ref_id");
            $model = new DesktopModel();
            $model->removeItemFromDesktop($authorizedUserId, $ref_id);
            $app->success("Removed item with ref_id=".$ref_id." from desktop.");
        } catch (Libs\DeleteFailed $e) {
            $app->halt(401, "Error: ".$e->getMessage(), -15);
        }
    });


    /**
     * Adds an item specified by ref_id to the users's desktop. The user must be the owner or at least has read access of the item.
     */
    $app->post('/desktop/overview', '\RESTController\libs\OAuth2Middleware::TokenRouteAuth',  function () use ($app) {
        $auth = new Auth\Util();
        $accessToken = $auth->getAccessToken();
        $authorizedUserId = $accessToken->getUserId();
        $request = $app->request();
        try {
            $ref_id = $request->params("ref_id");
            $model = new DesktopModel();
            $model->addItemToDesktop($authorizedUserId, $ref_id);
            $app->success("Added item with ref_id=".$ref_id." to the desktop.");
        } catch (Libs\UpdateFailed $e) {
            $app->halt(401, "Error: ".$e->getMessage(), -15);
        }
    });


});
