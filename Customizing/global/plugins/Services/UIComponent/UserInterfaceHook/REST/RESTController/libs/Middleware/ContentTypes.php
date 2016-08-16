<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer and T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */
namespace RESTController\libs\Middleware;


/**
 * Content Types
 *  Based on the SlimFramework ContentTypes implementation.
 *  But this actually returns assoziative arrays for JSON
 *  and XML content...
 */
class ContentTypes extends \Slim\Middleware {
  /**
   *
   */
  public function call() {
    $mediaType = $this->app->request()->getMediaType();
    if ($mediaType) {
      $env = $this->app->environment();
      $env['slim.input_original'] = $env['slim.input'];
      $env['slim.input']          = $this->parse($env['slim.input'], $mediaType);
    }
    $this->next->call();
  }


  /**
   *
   */
  protected function parse ($input, $contentType) {
    if (in_array($contentType, [ 'application/json', 'text/json' ])) {
      $result = $this->parseJSON($input);
      if ($result)
        return $result;
    }
    elseif (in_array($contentType, [ 'application/xml', 'text/xml' ])) {
      $result = $this->parseXML($input);
      if ($result)
        return $result;
    }

    return $input;
  }


  /**
   *
   */
  protected function parseJSON($input) {
    if (function_exists('json_decode')) {
      $result = json_decode($input, true);
      if (json_last_error() === JSON_ERROR_NONE)
        return $result;
    }
  }


  /**
   *
   */
  protected function parseXML($input) {
    if (function_exists('simplexml_load_string')) {
      $result = simplexml_load_string($input);
      if ($result != FALSE)
        if (function_exists('json_decode') && function_exists('json_encode'))
          return json_decode(json_encode($result), TRUE);
        else
          return $this->XML2Array($result);
    }
  }


  /**
   *
   */
  protected function XML2Array($xml) {
    $result = array () ;
    foreach ($xml as $key => $value) {
      $value = (array) $value ;
      if (isset($value[0]))
        $result [$key] = trim($value[0]) ;
      else
        $result [$key] = XML2Array($value , true);
    }

    return $result ;
  }
}
