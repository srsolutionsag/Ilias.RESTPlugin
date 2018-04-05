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
	 * @con_length     128
	 */
	protected $cache_id = '';
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
	public function getCacheId() {
		return $this->cache_id;
	}


	/**
	 * @param string $cache_id
	 *
	 * @return HashCacheEntry
	 */
	public function setCacheId($cache_id) {
		$this->cache_id = $cache_id;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getHash() {
		return $this->hash;
	}


	/**
	 * @param string $hash
	 *
	 * @return HashCacheEntry
	 */
	public function setHash($hash) {
		$this->hash = $hash;

		return $this;
	}
}