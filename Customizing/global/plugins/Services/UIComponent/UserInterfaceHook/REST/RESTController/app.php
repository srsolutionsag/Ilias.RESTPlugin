<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer and T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */
namespace RESTController;

// Include SLIM-Framework
require_once('Slim/Slim.php');

use \RESTController\database as Database;


/**
 * Class: RESTController
 *  This is the RESTController Slim-Application
 *  Handles all REST related logic and uses ILIAS
 *  Services to fetch requested data.
 *
 * Usage:
 *  require_once("<PATH-TO-THIS-FILE>". "/app.php");
 *  \RESTController\RESTController::registerAutoloader();
 *  $app = new \RESTController\RESTController("<PATH-TO-THIS-FILE>");
 *  $app->run();
 */
class RESTController extends \Slim\Slim {
  // Allow to re-use status messages and codes
  const MSG_NO_ROUTE  = 'There is no route matching this URI!';
  const ID_NO_ROUTE   = 'RESTController\RESTController::ID_NO_ROUTE';


  /**
   * Function: autoload($classname)
   *  PSR-0 autoloader for RESTController classes.
   *  Automatically adds a "models" subname into the namespace of \RESTController\core und
   *  @See \Slim\Slim::autoload(...)
   *  Register this outload via RESTController::registerAutoloader().
   *
   * Parameters:
   *  $className <String> - Fully quantified classname (includes namespace) of a class that needs to be loaded
   */
  public static function autoload($className) {
    // Fetch sub namespaces
    $subNames = explode('\\', $className);

    // Only load classes inside RESTController namespace
    if ($subNames[0] === __NAMESPACE__) {
      // (Core-) Extentions can leave-out the "models" subname in their namespace
      if ($subNames[1] == 'extensions' || $subNames[1] == 'core') {
        // Add 'Models' to class namespace
        array_splice($subNames, 3, 0, array('models'));
        array_shift($subNames);
        parent::autoload(implode($subNames, '\\'));

        // Fallback (without appending 'models')
        if (!class_exists($className, false))
          parent::autoload(substr($className, strlen(__NAMESPACE__)));
      }
      // Everything else gets forwarded directly to Slim
      else
        parent::autoload(substr($className, strlen(__NAMESPACE__)));
    }

    // Use Slim-Frameworks autoloder for non-RESTController classes
    else
      parent::autoload($className);
  }


  /**
   * Function: registerAutoloader()
   *  Register PSR-0 autoloader. Call this before doing $app = new RESTController();
   */
  public static function registerAutoloader() {
    // Attach RESTController autoloader
    spl_autoload_register(__NAMESPACE__.'\\RESTController::autoload');
  }


  /**
   * Constructor: RESTController($appDirectory, $userSettings)
   *  Creates a new instance of the RESTController. There should always
   *  be only one instance and a reference can be fetches via:
   *   RESTController::getInstance()
   *
   * Parameters:
   *  $appDirectory <String> - Directory in which the app.php is contained
   *  $userSettings <Array[Mixed]> - Associative array of application settings
   */
  public function __construct($appDirectory, array $userSettings = array()) {
    // Call parent (SLIM) constructor
    parent::__construct($userSettings);

    // Setup custom router, request- & response classes
    $this->setCustomContainers();

    // Set default template base-directory
    $this->view()->setTemplatesDirectory($appDirectory);

    // Set default 404 template
    $this->notFound(function () { $this->halt(404, self::MSG_NO_ROUTE, self::ID_NO_ROUTE); });

    // Setup error-handler
    $this->setErrorHandlers();

    // Disable fancy debug-messages
    $this->config('debug', false);
  }


  /**
   * Function: Run()
   *  This method starts the actual RESTController application, including the middleware stack#
   *  and the core Slim application, which includes route-handling, etc.
   */
  public function run() {
    // Set output-format
    $this->initResponseFormat();

    // Initialize ILIAS (if not created via restplugin.php)
    $this->initILIAS();

    # Configure the logger
    $this->initLogWriter();

    // Load routes
    $this->loadRoutes();

    // Log each access triggered
    $this->logPreRun();

    // Start the SLIM application
    parent::run();

    // Log each access triggered
    $this->logPostRun();
  }


  /**
   * Function: success(($data)
   *  This function should be used by any route that wants to return
   *  data after a successfull query. The application will be terminated
   *  afterwards, so make sure any required cleanup happens before
   *  a call to success(...).
   *
   *  @See RESTController->halt(...) for additional notes!
   *
   * Parameters:
   *  $data <String>/<Array[Mixed]> -
   */
  public function success($data) {
    // Delegate to halt(...)
    $this->halt(200, $data, null);
  }


  /**
   * Function: halt(($httpStatus, $data, $restStatus)
   *  This function should be used by any route that wants to return
   *  data or any kind of information after query/request has failed
   *  for some reason . The application will be terminated afterwards,
   *  so make sure any required cleanup happens before a call to halt(...).
   *
   *
   * Note 1:
   *  It is important to note, that this will imidiately send the given $data
   *  (as JSON, unless changed via response->setFormat(...)) and in addition
   *  will cause the application to be terminated by internally throwing
   * 'Slim\Exception\Stop'. This is to prevent any further data from 'leaking'
   *  to the client, which could invalidate the transmitted JSON object.
   *  In case of failure this also negates the requirement to manually invoke
   *  die() or exit() each time...
   *
   * Note 2:
   *  In the rare cases where this behaviour might not be usefull, there is also
   *  the options to directly access the response-object via $app->response() and
   *  (See libs\RESTResponse and Slim\Http\Response for additonal details)
   *  The Data will then be send either after the exiting the route-function or
   *  by manually throwing 'Slim\Exception\Stop'. (Not recommended)
   *  (Transmitting data this way should be used sparingly!)
   *
   * Note 3:
   *  Never use this method or access the $app->request() and $app->response()
   *  object from within a model, since this would make it difficult to reuse.
   *  Only use inside a route or IO-Class and pass data from/to models!
   *
   * Parameters:
   *  $httpStatus <Integer> -
   *  $data <String>/<Array[Mixed]> - [Optional]
   *  $restStatus <String> - [Optional]
   */
  public function halt($httpStatus, $data = null, $restStatus = 'halt') {
    // Do some pre-processing on the $data
    $response = libs\RESTResponse::responseObject($data, $restStatus);

    // Delegate transmission of response to SLIM
    parent::halt($httpStatus, $response);
  }


  /**
   * Function: AccessTokenDB()
   *  Returns true if access-tokens should be stored inside the database
   *  and looked up on access-request.
   *
   * Return:
   *  <Boolean> - True if access-token should be stored and looked up from DB, false otherwise
   */
  public function AccessTokenDB() {
    // Note: Some day this will be stored inside a config, to lazy now...
    return true;
  }


  /**
   * Function: displayError($msg, $code, $file, $line, $trace)
   *  Send the error-message given by the parameters to the clients
   *  and add a (critical) log-message to the active logfile.
   *
   * Parameters:
   *  $msg <String> - [Optional] Description of error/exception
   *  $code <Integer> - [Optional] Code of error/exception
   *  $file <String> - [Optional] File where the error/exception occured
   *  $line <Integer> - [Optional] Line in file where the error/exception occured
   *  $trace <String> - [Optional] Full (back-)trace (string) of error/exception
   */
  public function getError($error) {
    if ($error instanceof libs\RESTException)
      $error = array(
        'message'   => $error->getRESTMessage(),
        'status'    => $error->getRESTCode(),
        'data'      => $error->getRESTData(),
        'error'     => array(
          'message' => $error->getMessage(),
          'code'    => $error->getCode(),
          'file'    => str_replace('/', '\\', $error->getFile()),
          'line'    => $error->getLine(),
          'trace'   => str_replace('/', '\\', $error->getTraceAsString())
        )
      );

    elseif ($error instanceof \Exception)
      $error = array(
        'message'   => 'An exception was thrown!',
        'status'    => '\Exception',
        'error'     => array(
          'message' => $error->getMessage(),
          'code'    => $error->getCode(),
          'file'    => str_replace('/', '\\', $error->getFile()),
          'line'    => $error->getLine(),
          'trace'   => str_replace('/', '\\', $error->getTraceAsString())
        )
      );

    elseif (is_array($error))
      $error = array(
        'message'   => 'There is an error in the executed PHP-Script.',
        'status'    => 'FATAL',
        'error'     => array(
          'message' => $error['message'],
          'code'    => $error['type'],
          'file'    => str_replace('/', '\\', $error['file']),
          'line'    => $error['line'],
          'trace'   => null
        )
      );

    else
      $error = array(
        'message'   => 'Unkown error...',
        'status'    => 'UNKNOWN'
      );

    // Log error to file
    $this->log->critical($error);

    // Return error-object
    return $error;
  }


  /**
   * Function: logRun()
   *  Logs some valuable information for each access triggering the RESTController to run.
   */
  protected function logPreRun() {
    // Fetch all information that should be logged
    $log     = $this->getLog();
    $request = $this->request();
    $ip      = $request->getIp();
    $method  = $request->getMethod();
    $route   = $request->getResourceUri();
    $when    = date('d/m/Y, H:i:s', time());

    // Log additional information in debug-mode (with parameters)
    if ($log->getLevel() == \Slim\Log::DEBUG) {
      $parameters = $request->getParameter();
      $log->debug(sprintf(
        "[%s]: REST was called from '%s' on route '%s' [%s] with Parameters:\n%s",
        $when,
        $ip,
        $route,
        $method,
        print_r($parameters, true)
      ));
    }
    // Log access without request parameters
    else
      $log->info(sprintf(
        "[%s]: REST was called from '%s' on route '%s' [%s]...",
        $when,
        $ip,
        $route,
        $method,
        print_r($parameters, true)
      ));
  }


  /**
   * Function: logRun()
   *  Logs some valuable information for each access triggering the RESTController to run.
   */
  protected function logPostRun() {
    // Fetch logger
    $log     = $this->getLog();

    // Log additional information in debug-mode (with parameters)
    if ($log->getLevel() == \Slim\Log::DEBUG) {
      // Fetch all information that should be logged
      $request  = $this->request();
      $response = $this->response();
      $ip       = $request->getIp();
      $method   = $request->getMethod();
      $route    = $request->getResourceUri();
      $when     = date('d/m/Y, H:i:s', time());
      $status   = $response->getStatus();
      $headers  = $response->headers->all();
      $body     = $response->decode($response->getBody());

      // Output log
      $log->debug(sprintf(
        "[%s]: REST call from '%s' on route '%s' [%s] finished with:\nStatus: '%s'\nHeaders:\n%s\nBody:\n%s",
        $when,
        $ip,
        $route,
        $method,
        $status,
        print_r($headers, true),
        print_r($body, true)
      ));
    }
  }


  /**
   * Function: setCustomContainers()
   *  Attach custom 'containers' (singleton-instances) for
   *  logging, reading requests, writing responses and
   *  fetching available routes.
   */
  protected function setCustomContainers() {
    // Attach our custom RESTRouter, RESTRequest, RESTResponse
    $this->container->singleton('router',   function ($c) { return new libs\RESTRouter(); });
    $this->container->singleton('response', function ($c) { return new libs\RESTResponse(); });
    $this->container->singleton('request',  function ($c) { return new libs\RESTRequest($this->environment()); });

    // Add Content-Type middleware (support for JSON/XML requests)
    $contentType = new libs\Middleware\ContentTypes();
    $this->add($contentType);
  }

  /**
   * Configure custom LogWriter
   * @param $logFile - string consisting of full path + filename
   */
  protected function initLogWriter() {
    // Fetch config location from database
    try {
      $settings = Database\RESTconfig::fetchSettings(array('log_file', 'log_level'));
      $logFile   = $settings['log_file'];
      $logLevel  = $settings['log_level'];
    }
    catch (Libs\Exceptions\Database $e) { }

    // Use fallback values
    if (!isset($logLevel) || !is_string($logFile))
      $logFile = sprintf('%s/restplugin-%s.log', ILIAS_LOG_DIR, CLIENT_ID);
    if (!isset($logLevel))
      $logLevel = 'DEBUG';

    // Create file if it does not exist
    if (!file_exists($logFile)) {
      $fh = fopen($logFile, 'w');
      fclose($fh);
    }

    // Check wether file exists and is writeable
    if (!is_writable($logFile))
      $app->halt(500, sprintf('Can\'t write to log-file: %s (Make sure file exists and is writeable by the PHP process)', $logFile));

    // Open the logfile for writing to using Slim
    $logWriter = new \Slim\LogWriter(fopen($logFile, 'a'));
    $log       = $this->getLog();
    $log->setWriter($logWriter);

    // Set logging level
    switch (strtoupper($logLevel)) {
      case 'EMERGENCY':
        $log->setLevel(\Slim\Log::EMERGENCY);
        break;
      case 'ALERT':
        $log->setLevel(\Slim\Log::ALERT);
        break;
      case 'CRITICAL':
        $log->setLevel(\Slim\Log::CRITICAL);
        break;
      case 'FATAL':
        $log->setLevel(\Slim\Log::FATAL);
        break;
      case 'ERROR':
        $log->setLevel(\Slim\Log::ERROR);
        break;
      case 'WARN':
        $log->setLevel(\Slim\Log::WARN);
        break;
      case 'NOTICE':
        $log->setLevel(\Slim\Log::NOTICE);
        break;
      case 'INFO':
        $log->setLevel(\Slim\Log::INFO);
        break;
      case 'DEBUG':
      default:
        $log->setLevel(\Slim\Log::DEBUG);
        break;
    }
  }


  /**
   * Function: initResponseFormat()
   *  Tries to autodetect the preffered output-format.
   *  If the request-route ends in .json or .xml
   *  this format is used, else the request content-type
   *  is used. If none is available, JSON is used as
   *  default fallback.
   */
  protected function initResponseFormat() {
    // Set output-format
    $requestURI    = $this->request()->getResourceUri();
    $routeFormat   = $this->router()->getResponseFormat($requestURI);
    $requestFormat = $this->request()->getFormat();

    // Prefer format set via route 'file-ending'
    if ($routeFormat)
      $this->response()->setFormat($routeFormat);
    // Use request format as fallback
    elseif ($requestFormat)
      $this->response()->setFormat($requestFormat);
    // Lastly fallback to json
    else
      $this->response()->setFormat('json');
  }


  /**
   * Function: initILIAS()
   *  Makes sure ILIAS was initialized, eg.
   *  when this has not already been done
   *  by the restplugin.php
   */
  protected function initILIAS() {
    // Initialize ILIAS (if not created via restplugin.php)
    if (!defined('CLIENT_ID')) {
      // Fetch ILIAS client from token
      $client   = libs\RESTilias::getTokenClient($this->request());

      // Initialize ilias with given client (null means: the client given as GET or COOKIE like normal)
      ob_start();
      libs\RESTilias::initILIAS($client);
      ob_end_clean();
    }
  }


  /**
   * Function: loadRoutes()
   *  Makes a global $app variable available and loads
   *  all route php-files.
   */
  protected function loadRoutes() {
    // Make $app variable available in all route-files
    $app = $this;

    // Load core routes
    foreach (glob(realpath(__DIR__).'/core/*/routes/*.php') as $filename)
      include_once($filename);

    // Load extension routes
    foreach (glob(realpath(__DIR__).'/extensions/*/routes/*.php') as $filename)
      include_once($filename);
  }


  /**
   * Function: setErrorHandlers()
   *  Registers both a custom error-handler for errors/exceptions caughts by
   *  SLIM as well as registering a shutdown function for other FATAL errors.
   *  Additionally also disable PHP's display_errors flag!
   */
  protected function setErrorHandlers() {
    // Set default error-handler for exceptions caught by SLIM
    $this->error(function (\Exception $error) {
      // Stop executing on error
      $this->halt(500, $this->getError($error));
    });

    // Set default error-handler for any error/exception not caught by SLIM
    ini_set('display_errors', false);
    register_shutdown_function(function () {
      // Fetch latch error
      $error = error_get_last();

      // Check wether the error should to be displayed
      $allowed = array(
        E_ERROR         => 'E_ERROR',
        E_PARSE         => 'E_PARSE',
        E_CORE_ERROR    => 'E_CORE_ERROR',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_USER_ERROR    => 'E_USER_ERROR'
      );

      // Log and display error?
      if (array_key_exists($error['type'], $allowed)) {
        // Output formated error via echo
        header('content-type: application/json');
        echo json_encode($this->getError($error));
      }

      // FixIt: Is this working?
      $this->getLog()->fatal($error);
    });
  }
}
