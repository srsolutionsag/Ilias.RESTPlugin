<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer, T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */
namespace RESTController\libs;


/**
 * Class: RESTLib
 *  This class provides some common utility functions not
 *  directly related to any model.
 */
class RESTLib {
  /**
   * Function: getClientCertificate()
   *  Utility method to nicely fetch client-certificate (ssl) data from
   *  gfobal namespace and preformat it...
   *
   * Return:
   *  <Array[String]> - See below...
   */
  public static function FetchClientCertificate() {
    // Build a more readable ssl client-certificate array...
    return array(
      verify  => $_SERVER['SSL_CLIENT_VERIFY'],
      serial  => $_SERVER['SSL_CLIENT_M_SERIAL'],
      issuer  => $_SERVER['SSL_CLIENT_I_DN'],
      subject => $_SERVER['SSL_CLIENT_S_DN'],
      expires => $_SERVER['SSL_CLIENT_V_END'],
      ttl     => $_SERVER['SSL_CLIENT_V_REMAIN']
    );
  }


  /**
   * Function: CheckComplexRestriction($pattern, $subjects, $delimiter)
   *  Checks if the subjects element(s) are all convered by the restrictions given by pattern.
   *  Pattern can either be a regular expressen, in which case all elements of subject are preg_match()'ed
   *  or a string-list which must contain all subject elements. (List-Delimiter can be given as parameter)
   *  (Used to check if requested scope is covered by client-scope, ip and/or user is allowed to use a client, etc...)
   *
   * Parameters:
   *  $pattern <String> - A regular expression that all subject(s) need to match against or is string-list
   *                      with all must contain all subject(s)
   *  $subjects <String>/<Array[String]> - Subjects that should be checks for the restrictions given by pattern
   *  $delimiter <String> - Optional delimiter used to explode() the restriction-list (pattern) if not a regular-expression
   *
   * Return:
   *  <Boolean> - If ALL subjects are covered by pattern true will be returned, otherwise false
   */
  public static function CheckComplexRestriction($pattern, $subjects, $delimiter = ',') {
    // Treat all subjects as array (easer to read code...)
    if (!is_array($subjects))
      $subjects = array($subjects);

    // No pattern set -> no restriction set
    if (!isset($pattern) || $pattern === false || strlen($pattern) == 0)
      return true;

    // Restriction is given as regex?
    elseif (preg_match('/^\/.*\/$/', $pattern) == 1) {
      // Check if ALL given subjects match the given restriction
      foreach ($subjects as $subject)
        if (preg_match($pattern, $subject) != 1)
          return false;
    }

    // Restriction is given as (string-) list of strings
    else {
      // Extract list-items (string list with given delimiter)
      $patterns = explode($delimiter, $pattern);

      // Check if ALL given subjects match the given restriction
      foreach ($subjects as $subject)
        if (!in_array($subject, $patterns))
          return false;
    }

    // Not returned by now -> means all subjects matched given pattern/restrictions
    return true;
  }


  /**
   * Function: CheckSimpleRestriction($pattern, $subject)
   *  Checks if a given parameter (subject) matches the given setting (pattern)
   *  or if the settings is disabled anyway.
   *
   * Parameters:
   *  $pattern <String> - The settings that needs to be matched
   *  $subject <Boolean>/<String> - The parameter that need to match the given setting
   *
   * Return:
   *  <Boolean> True if subject matches pattern (or pattern or subject is disabled), false otherwise
   */
  public static function CheckSimpleRestriction($pattern, $subject) {
    if (!isset($pattern) || $pattern === false || strlen($pattern) == 0 || $pattern == $subject)
      return true;
    else
      return false;
  }


  public static function fromMixed($xml, $mixed, $domElement) {
		if (is_array($mixed)) {
			foreach( $mixed as $index => $mixedElement ) {

				if ( is_int($index) ) {
					if ( $index == 0 ) {
						$node = $domElement;
					} else {
						$node = $xml->createElement($domElement->tagName);
						$domElement->parentNode->appendChild($node);
					}
				}

				else {
          if (preg_match('/^[^A-Za-z0-9]/', $index)) {
            $node = $xml->createElement('element' . rand());
            $domElement->appendChild($node);
          }
          else {
		        $node = $xml->createElement($index);
            $domElement->appendChild($node);
          }
				}

				self::fromMixed($xml, $mixedElement, $node);

			}
		} else {
			$domElement->appendChild($xml->createTextNode($mixed));
		}

	}


  /**
   * Static-Function: Array2XML($array)
   *  Converts the given input assoziative array to an xml
   *  string-representation of said array.
   *
   * Parameters:
   *  simpleXML <SimpleXMLElement> - Assoziative array to be converted
   *
   * Return:
   *  <String> - XML string-representation of converted array
   */
  public static function Array2XML($array) {

    try {
      $xml = new \DOMDocument('1.0', 'utf-8');
      self::fromMixed($xml, $array, $xml);
      return $xml->saveXML();

      /*
      $xml  = new \DOMDocument('1.0', 'utf-8');
      $root = $xml->createElement('response');
      $xml->appendChild($root);


      $node = $xml->createElement('element');
      $text = $xml->createTextNode('value');
      $node->appendChild($text);
      $root->appendChild($node);
      //self::Array2XML_Recursive($xml, $root, $array);


      return $xml->saveXml();
      */
    }
    catch (\Exception $e) {
      var_dump($e);
      die;
    }


    /*
    $xml = new DOMDocument('1.0', 'utf-8');
    $root = $xml->createElement('top');
    $xml->appendChild($root);
    foreach ($arr as $k => $v) {
      $node = $xml->createelement($k);
      $text = $xml->createTextNode($v);
      $node->appendChild($text);
      $root->appendChild($node);
    }
    echo $xml->saveXml();
    */
  }


  /**
   *
   */
  public static function Array2XML_Recursive($xml, $root, $array) {
    if (is_array($array)) {
      foreach ($array as $key => $value) {
        $node = $xml->createElement($key);

        if (is_array($value)) {
          self::Array2XML_Recursive($xml, $node, $value);
          $root->appendChild($node);
        }
        else {
          $text = $xml->createTextNode('TEXT');
          $node->appendChild($text);
          $root->appendChild($node);
        }
      }
    }
    else {
      $text = $xml->createTextNode('TEXT');
      $root->appendChild($text);
    }
  }


  /**
   * Static-Function: XML2Array($string)
   *  Converts the imput string to an assoziative array.
   *
   * Parameters:
   *  $string <String> - String representation of XML data
   *
   * Return:
   *  <Array> - Assoziative array representing the input xml object
   */
  public static function XML2Array($string) {
    // Try to convert string to SimpleXMLElement
    $result = simplexml_load_string($input);

    // Conversion from string to SimpleXMLElement succeded
    if ($result != FALSE) {
      if (function_exists('json_decode') && function_exists('json_encode'))
        return json_decode(json_encode($simpleXML), TRUE);
      else
        return self::XML2Array_Recursive($simpleXML);
    }
  }


  /**
   * Static-Function: XML2Array_Recursive($simpleXML)
   *  Recursively convert SimpleXMLElement object to
   *  an assoziative array.
   *
   * Parameters:
   *  simpleXML <SimpleXMLElement> - XML object to convert to array
   *
   * Return:
   *  <Array> - Assoziative array representing the input xml object
   */
  protected static function XML2Array_Recursive($simpleXML) {
    // Iterate input SimpleXMLElement
    $result = array () ;
    foreach ($simpleXML as $key => $value) {
      // Convert to array-object
      $value = (array) $value ;

      // Recursively convert SimpleXMLElement into array
      if (isset($value[0]))
        $result[$key] = trim($value[0]) ;
      else
        $result[$key] = XML2Array_Recursive($value);
    }

    // Return finally
    return $result ;
  }
}
