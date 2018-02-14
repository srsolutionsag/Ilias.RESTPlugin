<?php

namespace RESTController\extensions\ILIASApp\V2;

require_once './Services/ActiveRecord/class.ActiveRecord.php';

use ActiveRecord;

/**
 * Class HashCacheEntry
 *
 * Represents a single hash cache entry.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class HashCacheEntry extends ActiveRecord {

	/**
	 * @var string $path
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_is_notnull true
	 * @con_fieldtype  text
	 * @con_length     3072
	 */
	protected $path = '';
	/**
	 * @var string $hash
	 *
	 * @con_has_field  true
	 * @con_is_notnull true
	 * @con_fieldtype  text
	 * @con_length     128
	 */
	protected $hash = '';


	public static function returnDbTableName() {
		return 'ui_uihk_rest_hashcache';
	}


	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}


	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}


	/**
	 * @return string
	 */
	public function getHash() {
		return $this->hash;
	}


	/**
	 * @param string $hash
	 */
	public function setHash($hash) {
		$this->hash = $hash;
	}
}