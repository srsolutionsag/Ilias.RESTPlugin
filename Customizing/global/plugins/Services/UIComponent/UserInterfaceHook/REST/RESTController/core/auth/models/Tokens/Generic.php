<?php
/**
 * ILIAS REST Plugin for the ILIAS LMS
 *
 * Authors: D.Schaefer and T.Hufschmidt <(schaefer|hufschmidt)@hrz.uni-marburg.de>
 * Since 2014
 */
namespace RESTController\core\auth\Tokens;

// This allows us to use shortcuts instead of full quantifier
use \RESTController\libs as Libs;
use \RESTController\core\auth\Exceptions as Exceptions;


/**
 * Class: Generic (-Token)
 *  (Convieved) Abstract class for common access- and refresh-token code.
 */
class Generic extends Base {
  // Allow to re-use status messages and codes
  const MSG_EXPIRED = 'Token has expired.';
  const ID_EXPIRED  = 'RESTController\\core\\auth\\Generic::ID_EXPIRED';
  const MSG_INVALID = 'Token is invalid.';
  const ID_INVALID  = 'RESTController\\core\\auth\\Generic::ID_INVALID';


  // Stores the settings attached to this token (salt and default TTL)
  protected $tokenSettings;

  // List of fields (keys) for this kind of token
  protected static $fields = array(
    'user_id',
    'ilias_client',
    'api_key',
    'type',
    'misc',
    'ttl',
    's',
    'h'
  );

  // Store username in addition to user-id (only looked-up once)
  protected $username = null;


  /**
   * Constructor:
   *  Creates a new 'generic' token.
   *
   * Parameters:
   *  $tokenSettings <Settings> - Internal settings of this token
   */
  protected function __construct($tokenSettings) {
    $this->tokenSettings = $tokenSettings;
  }


  /**
   * Static-Function: fromMixed($tokenSettings, $tokenArray)
   *  Generates a Generic-Token from given input parameters.
   *  Expects settings-object and token-data as array.
   *
   * Parameters:
   *  $tokenSettings <Settings> - Internal settings of this token
   *  $tokenArray <Array[Mixed]> - Array of string (key & value) elements representing a valid token
   *
   * Return:
   *  <GenericToken> - Generated Generic-Token
   */
  public static function fromMixed($tokenSettings, $tokenArray) {
    // Generate new token from token-data as array
    $genericToken = new self($tokenSettings);
    $genericToken->setToken($tokenArray);

    // Return new object
    return $token;
  }


  /**
   * Static-Function: fromFields($tokenSettings, $user_id, $ilias_client, $api_key, $type, $misc, $lifetime)
   *  Generates a Generic-Token from given input parameters.
   *  Expects settings-object and token-data as additional parameters.
   *
   * Parameters:
   *  $tokenSettings <Settings> - Internal settings of this token
   *  $user_id <String> - User-Id that should be attached to the token
   *  $ilias_client <String> - ILIAS Client-Id that should be attached to the token
   *  $api_key <String> - API-Key that should be attached to the token
   *  $type <String> - Type that should be attached to token
   *  $misc <String> - Misc data that should be attached to token
   *  $lifetime <Integer> - Lifetime that should be attached to token (get invalid after expiration)
   *
   * Return:
   *  <GenericToken> - Generated Generic-Token
   */
  public static function fromFields($tokenSettings, $user_id, $ilias_client = null, $api_key, $type = null, $misc = null, $lifetime = null) {
    // Generate new token from token-data as parameters
    $genericToken = new self($tokenSettings);
    $tokenArray   = $genericToken->generateTokenArray($user_id, $ilias_client, $api_key, $type, $misc, $lifetime);
    $genericToken->setToken($tokenArray);

    // Return new object
    return $genericToken;
  }


  /**
   * Function: setToken($tokenMixed)
   *  Updates internal token-array with data from token
   *  represented by $tokenMix.
   *
   * Parameters:
   *  $tokenMixed <String>/<Array[Mixed]> - Valid token-string or token-array
   */
  public function setToken($tokenMixed) {
    // Convert input to array data
    if (is_string($tokenMixed)) $tokenArray = self::deserializeToken($tokenMixed);
    else                        $tokenArray = $tokenMixed;

    // Check validity of input
    if (!$this->isValidTokenArray($tokenArray))
      throw new Exceptions\TokenInvalid(self::MSG_INVALID, self::ID_INVALID);

    // Update internal storage
    parent::setToken($tokenArray);
  }


  /**
   * Function: getTokenString()
   *  Returns this token in string format.
   *
   * Return:
   *  <String> - String representing this token
   */
  public function getTokenString() {
    return self::serializeToken($this->tokenArray);
  }


  /**
   * Function: setEntry($field, $value)
   *  Update the internal token-data stored in given $field (key)
   *  with given $value. Additonally makes sure the token stays valid
   *  by updating its hash.
   *
   * Parameters:
   *  $field <String> - Which key of the internal token-data should be updated
   *  $field <Mixed> - What should be stored as new token-data for given key
   */
  public function setEntry($field, $value) {
    // Chaching hash-value is not allowed
    if (strtolower($field) != 'h') {
      // Update value
      parent::setEntry($field, $value);

      // Update token hash
      $this->tokenArray['h'] = $this->getHash($this->tokenArray);
    }
  }


  /**
   * Getter-Functions:
   *  getUserName() - Returns username attached to stored user-id
   *  getUserId() - Return stored user-id
   *  getApiKey() - Return stored api-key
   *  getIliasClient() - Return stored ilias client-id
   */
  public function getUserName() {
    // Fetch username once
    if (!$this->username)
      $this->username = Libs\RESTLib::getUserName($this->tokenArray['user_id']);

    // Afterwars simply return stored value
    return $this->username;
  }
  public function getUserId() {
    return $this->tokenArray['user_id'];
  }
  public function getApiKey() {
    return $this->tokenArray['api_key'];
  }
  public function getIliasClient() {
    return $this->tokenArray['ilias_client'];
  }


  /**
   * Function: isValid()
   *  Checks wether token data contained in this token (and thus the token itsself) is
   *  valid, by checking wether the hash (which can only be generated using the tokens
   *  internal salt) corresponds to the calcluated hash of the internal token-array.
   * Note: An expired token may still be valid, or the other way around
   *       a valid token might still be expired.
   *
   * Return:
   *  <Boolean> - True if token seems to be valid, false otherwise
   */
  public function isValid() {
    return $this->isValidTokenArray($this->tokenArray);
  }


  /**
   * Function: isExpired()
   *  Checks wether the token is expired given the internal ttl-date
   *  AND if the token is actually valid (first).
   *
   * Return:
   *  <Boolean> - True if token is valid and not expired yet
   */
  public function isExpired() {
    return !($this->isValid() && intval($this->tokenArray['ttl']) > time());
  }


  /**
   * Function: getRemainingTime()
   *  Returns the remaining time (in seconds) until this token
   *  will be retired.
   *
   * Return:
   *  <Integer> - Remaining time in seconds until tthis oken expires
   */
  public function getRemainingTime() {
    return (!$this->isExpired()) ? intval($this->tokenArray['ttl']) - time() : 0;
  }


  /**
   * Function: refresh($ttl)
   *  Resets the TTL of this token to either the given duration
   *  or the tokens default TTL.
   *  This is different from setEntry('ttl', $ttl) in that it also
   *  regenrates the tokens random-string ('s'-field) component.
   *
   * Parameters:
   *  $ttl <Integer> - Remaining duration this token should have.
   */
  public function refresh($ttl = null) {
    // Only valid tokens can be refreshed
    if (!$this->isValid())
      return null;

    // Extract original data
    $user_id      = $this->tokenArray['user_id'];
    $ilias_client = $this->tokenArray['ilias_client'];
    $api_key      = $this->tokenArray['api_key'];
    $type         = $this->tokenArray['type'];
    $misc         = $this->tokenArray['misc'];

    // Update $this from given data, but with reset ttl (null -> default-ttl)
    $token = $this->generateTokenArray($user_id, $ilias_client, $api_key, $type, $misc, $ttl);
    $this->setToken($token);
  }


  /**
   * Function: generateTokenArray($user_id, $ilias_client, $api_key, $type, $misc, $lifetime)
   *  Generates a token-array for the given input parameters, for internal use only.
   *
   * Parameters:
   *  $user_id <String> - User-Id that should be attached to the token
   *  $ilias_client <String> - ILIAS Client-Id that should be attached to the token
   *  $api_key <String> - API-Key that should be attached to the token
   *  $type <String> - Type that should be attached to token
   *  $misc <String> - Misc data that should be attached to token
   *  $lifetime <Integer> - Lifetime that should be attached to token (get invalid after expiration)
   *
   * Return:
   *  <Array[Mixed]> - Generated token-array (Eg. for internal storage)
   */
  protected function generateTokenArray($user_id, $ilias_client = null, $api_key, $type = null, $misc = null, $lifetime = null) {
    // Apply default values
    if ($ilias_client == null) $ilias_client  = CLIENT_ID;
    if ($misc         == null) $misc          = 'generic';
    if ($lifetime     == null) $lifetime      = $this->tokenSettings->getTTL();

    // Generate random string to make re-hashing token "difficult"
    $randomStr = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, 25);

    // Generate token-array
    $tokenArray = array(
      'user_id'       => $user_id,
      'ilias_client'  => $ilias_client,
      'api_key'       => $api_key,
      'type'          => $type,
      'misc'          => $misc,
      'ttl'           => strval(time() + ($lifetime * 60)),
      's'             => $randomStr
    );

    // Generate hash for token
    $tokenArray['h'] = $this->getHash($tokenArray);

    // Return created token-array
    return $tokenArray;
  }


  /**
   * Function: getHash($tokenArray)
   *  Generates a unique, non-reverseable hash that can only
   *  be generated knowing the secret salt.
   *
   * Parameters:
   *  $tokenArray <Array[Mixed]> - Token-array for which to generate hash
   *
   * Return:
   *  <String> - Hash generated for this token
   */
  protected function getHash($tokenArray) {
    // Concat all token-array keys to string
    $hashStr = sprintf(
      '%s%s%s%s%s%s%s%s',
      $this->tokenSettings->getSalt(),
      $tokenArray['user_id'],
      $tokenArray['ilias_client'],
      $tokenArray['api_key'],
      $tokenArray['type'],
      $tokenArray['misc'],
      $tokenArray['ttl'],
      $tokenArray['s']
    );

    // Add additional salt and generate non-invertable hash
    return hash('sha256', $hashStr);
  }


  /**
   * Function: isValidTokenArray($tokenArray)
   *  Utility-Function used to check correctness of data in token-array.
   *  It compares the hash stored inside token-array with the hash generated
   *  from token-array.
   *
   * Parameters:
   *  $tokenArray <Array[Mixed]> - Token-array which should be checked
   *
   * Return:
   *  <Boolean> - True if token is valid (or at least consistent)
   */
  protected function isValidTokenArray($tokenArray) {
    return (count($tokenArray) == count(self::$fields) && $this->getHash($tokenArray) == $tokenArray["h"]);
  }


  /**
   * Function: serializeToken($tokenArray)
   *  Converts the token-array into a token-string.
   *  Both represent the same token object, but have different use-cases.
   *   array - internal storage
   *   string - data transmission
   *
   * Parameters:
   *  $tokenArray <Array[Mixed]> - Token-array which should be converted to string
   *
   * Return:
   *  <String> - Converted token-array
   */
  public static function serializeToken($tokenArray) {
    // Concat all token-array keys to string
    $tokenStr = sprintf(
      '%s,%s,%s,%s,%s,%s,%s,%s',
      str_replace(',', '', $tokenArray['user_id']),
      str_replace(',', '', $tokenArray['ilias_client']),
      str_replace(',', '', $tokenArray['api_key']),
      str_replace(',', '', $tokenArray['type']),
      str_replace(',', '', $tokenArray['misc']),
      str_replace(',', '', $tokenArray['ttl']),
      str_replace(',', '', $tokenArray['s']),
      str_replace(',', '', $tokenArray['h'])
    );

    // Return serialized token-array
    return urlencode(base64_encode($tokenStr));
  }


  /**
   * Function: deserializeToken($tokenString)
   *  Converts the token-string into a token-array.
   *  Both represent the same token object, but have different use-cases.
   *   array - internal storage
   *   string - data transmission
   *
   * Parameters:
   *  $tokenString <String> - Token-string that should be converted to an array
   *
   * Return:
   *  <Array[Mixed]> - Converted token-string
   */
  public static function deserializeToken($tokenString) {
    // Deserialize token-string
    $tokenPartArray = explode(',', base64_decode(urldecode($tokenString)));

    // Reconstruct token-array from exploded string
    if (count($tokenPartArray) == count(self::$fields)) {
      return array(
        'user_id'       =>  $tokenPartArray[0],
        'ilias_client'  =>  $tokenPartArray[1],
        'api_key'       =>  $tokenPartArray[2],
        'type'          =>  $tokenPartArray[3],
        'misc'          =>  $tokenPartArray[4],
        'ttl'           =>  $tokenPartArray[5],
        's'             =>  $tokenPartArray[6],
        'h'             =>  $tokenPartArray[7]
      );
    }
    else
      throw new Exceptions\TokenInvalid(self::MSG_INVALID_SIZE, self::ID_INVALID_SIZE);
  }
}
