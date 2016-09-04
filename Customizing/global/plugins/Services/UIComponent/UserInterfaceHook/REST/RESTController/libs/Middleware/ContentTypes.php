<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer and T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */
namespace RESTController\libs\Middleware;

// This allows us to use shortcuts instead of full quantifier
use \RESTController\libs as Libs;


/**
 * Content Types
 *  Based on the SlimFramework ContentTypes implementation.
 *  But this actually returns  arrays for JSON
 *  and XML content...
 */
class ContentTypes extends \Slim\Middleware {
  /**
   * Function: call()
   *  When used as middleware this function will be called once
   *  the middleware is executed.
   */
  public function call() {
    // Fetch request content-type from headers...
    $mediaType = $this->app->request()->getMediaType();
    if ($mediaType) {

      // Store original request and try to convert request to an array
      $env = $this->app->environment();
      $env['slim.input_original'] = $env['slim.input'];
      $env['slim.input']          = $this->parse($env['slim.input'], $mediaType);
    }

    // Invoke next middleware
    $this->next->call();
  }


  /**
   * Function: parse($input, $contentType)
   *  Parses the given string input to an associative array
   *  if the given content-type is supported (JSON, XML).
   *  Note: x-www-form-urlencoded is already supported by Slim.
   *
   * Parameters:
   *  input <Sting> - Request payload to be converted to assoc-array
   *  contentType <String> -
   *
   * Return:
   *  <Array> - Converted assoc-array payload
   */
  protected function parse($input, $contentType) {
    // Convert JSON
    if (in_array($contentType, [ 'application/json', 'text/json' ])) {
      $result = $this->parseJSON($input);
      if ($result)
        return $result;
    }

    // Convert XML
    elseif (in_array($contentType, [ 'application/xml', 'text/xml' ])) {
      $result = $this->parseXML($input);
      if ($result)
        return $result;
    }

    // Return (converted) input
    return $input;
  }


  /**
   * Function: parseJSON($input, $contentType)
   *  Converts the request payload to an assoc-array
   *  if it is an a valid JSON format.
   *
   * Parameters:
   *  input <Sting> - Request payload to be converted to assoc-array
   *
   * Return:
   *  <Array> - Converted assoc-array payload
   */
  protected function parseJSON($input) {
    if (function_exists('json_decode')) {
      $result = json_decode($input, true);
      if (json_last_error() === JSON_ERROR_NONE)
        return $result;
    }
  }


  /**
   * Function: parseXML($input)
   *  Converts the request payload to an assoc-array
   *  if it is an a valid XML format.
   *
   * Parameters:
   *  input <Sting> - Request payload to be converted to assoc-array
   *
   * Return:
   *  <Array> - Converted assoc-array payload
   */
  protected function parseXML($input) {
    if (function_exists('simplexml_load_string')) {
      return Libs\RESTLib::XML2Array($result);
    }
  }
}
