<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer, S.Schneider and T. Hufschmidt <(schaefer|schneider|hufschmidt)@hrz.uni-marburg.de>
 * 2014-2015
 */
namespace RESTController\core\auth;

// This allows us to use shortcuts instead of full quantifier
use \RESTController\libs as Lib;


// Group as version-1 implementation
$app->group('/v1', function () use ($app) {

// Group as oauth2 implementation
$app->group('/oauth2', function () use ($app) {

/**
 * Route: /v1/oauth2/auth
 * Description:
 *  (RCF6749) Authorization Endpoint, used by the following grant-types:
 *   - authorization code grant
 *   - implicit grant type flows
 *  See http://tools.ietf.org/html/rfc6749
 * Method: POST
 * Auth:
 * Parameters:
 * Response:
 */
$app->post('/auth', function () use ($app) {
    $model = new OAuth2Model();
    $request = $app->request();
    $response_type = $request->params('response_type');

    // Type: Authorization grant
    if ($response_type == "code")
        $model->handleAuthorizationEndpoint_authorizationCode($app);
    // Type: Implicit grant
    elseif ($response_type == "token")
        $model->handleAuthorizationEndpoint_implicitGrant($app);
});


/**
 * Route:
 * Description:
 *
 * Method:
 * Auth:
 * Parameters:
 * Response:
 */
/*
 * Authorization Endpoint - GET part.
 *
 * This part covers only the first section of the auth flow and is included here,
 * s.t. clients can initiate the "authorization or implicit grant flow" with a GET request.
 * The flow after calling "oauth2loginform" continues with the POST version of "oauth2/auth".
 */
$app->get('/auth', function () use ($app) {
    $request = $app->request();
    $apikey = $_GET['api_key']; // Issue: Standard ILIAS Init absorbs client_id GET request field
    $client_redirect_uri = $_GET['redirect_uri'];
    $response_type = $_GET['response_type'];

    if ($response_type == "code") {
        if ($apikey && $client_redirect_uri && $response_type){
            OAuth2Model::render($app, 'REST OAuth - Login für Tokengenerierung', 'oauth2loginform.php', array(
                'api_key' => $apikey,
                'redirect_uri' => $client_redirect_uri,
                'response_type' => $response_type
            ));
        }

    } else if ($response_type == "token") { // implicit grant
        if ($apikey && $client_redirect_uri && $response_type){
            OAuth2Model::render($app, 'REST OAuth - Login für Tokengenerierung', 'oauth2loginform.php', array(
                'api_key' => $apikey,
                'redirect_uri' => $client_redirect_uri,
                'response_type' => $response_type
            ));
        }
    }
});


/**
 * Route:
 * Description:
 *
 * Method:
 * Auth:
 * Parameters:
 * Response:
 */
/*
 * Token Endpoint
 *
 * Supported grant types:
 *  - Resource Owner(User),
 *  - Client Credentials and
 *  - Authorization Code Grant
 *
 * see http://tools.ietf.org/html/rfc6749
*/
$app->post('/token', function () use ($app) {
    $request = new Lib\RESTRequest($app);
    $model = new OAuth2Model();

    $app->log->debug("Entering Token-Endpoint ... GC: ".$request->getParam('grant_type'));
    if ($request->getParam('grant_type') == "password")
        $model->handleTokenEndpoint_userCredentials($app, $request);
    elseif ($request->getParam('grant_type') == "client_credentials")
        $model->handleTokenEndpoint_clientCredentials($app, $request);
    elseif ($request->getParam('grant_type') == "authorization_code")
        $model->handleTokenEndpoint_authorizationCode($app, $request);
    elseif ($request->getParam('grant_type') == "refresh_token")
        $model->handleTokenEndpoint_refreshToken2Bearer($app);

});


/**
 * Route:
 * Description:
 *
 * Method:
 * Auth:
 * Parameters:
 * Response:
 */
/*
 * Refresh Endpoint
 *
 * This endpoint allows for exchanging a bearer token with a long-lasting refresh token.
 * Note: a client needs the appropriate permission to use this endpoint.
 */
$app->get('/refresh', '\RESTController\libs\AuthMiddleware::authenticate', function () use ($app) {
    $env = $app->environment();
    $uid = Lib\RESTLib::loginToUserId($env['user']);
    $response = new Oauth2Response($app);
    global $ilLog;
    $ilLog->write('Requesting new refresh token for user '.$uid);

    // Create new refresh token
    $bearerToken = $env['token'];
    $model = new OAuth2Model();
    $refreshToken = $model->getRefreshToken($bearerToken);

    $response->setHttpHeader('Cache-Control', 'no-store');
    $response->setHttpHeader('Pragma', 'no-cache');
    $response->setField("refresh_token",$refreshToken);
    $response->send();
});


/**
 * Route:
 * Description:
 *
 * Method:
 * Auth:
 * Parameters:
 * Response:
 */
/*
 * Token-info route
 *
 * Tokens obtained via the implicit code grant MUST by validated by the Javascript client
 * to prevent the "confused deputy problem".
 */
$app->get('/tokeninfo', function () use ($app) {
    $model = new OAuth2Model();
    $model->handleTokeninfoRequest($app);
});


// Enf-Of /oauth2-group
});


/**
 * Route:
 * Description:
 *
 * Method:
 * Auth:
 * Parameters:
 * Response:
 */
/*
 * rtoken2bearer: Allows for exchanging an ilias session with a bearer token.
 * This is used for administration purposes.
 */
$app->post('/ilauth/rtoken2bearer', function () use ($app) {
    $model = new OAuth2Model();
    $model->handleRTokenToBearerRequest($app);
});


// Enf-Of /v1-group
});
