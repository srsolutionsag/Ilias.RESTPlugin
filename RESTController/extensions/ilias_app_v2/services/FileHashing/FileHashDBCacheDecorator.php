<?php

namespace RESTController\extensions\ILIASApp\V2;

require_once __DIR__ . '/SHA256FileHashProvider.php';
require_once __DIR__ . '/FileHashProvider.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/services/FileHashing/entity/HashCacheEntry.php';

/**
 * Class FileHashDBCacheDecorator
 *
 * @package RESTController\extensions\ILIASApp\V2
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class FileHashDBCacheDecorator implements FileHashProvider {

	/**
	 * @var FileHashProvider $fileHashProvider
	 */
	private $fileHashProvider;


	/**
	 * FileHashDBCacheDecorator constructor.
	 *
	 * @param FileHashProvider $fileHashProvider
	 */
	public function __construct(FileHashProvider $fileHashProvider) { $this->fileHashProvider = $fileHashProvider; }


	/**
	 * Call the given hash provider to generate a hash and
	 * cache the result in the db afterwards.
	 *
	 * @param string $filePath The path of the file which should be hashed.
	 *
	 * @return string   The hexadecimal hash representation fetch from the cache.
	 */
	public function hash($filePath) {
		$cacheId = hash('sha256', $filePath);
		$hash = $this->fetch($cacheId);
		if($hash === '') {
			$hash = $this->fileHashProvider->hash($filePath);
			$this->cache($cacheId, $hash);
		}

		return $hash;
	}


	/**
	 * Stores the cache id and the hash in the db.
	 *
	 * @param string $cacheId The cache id of the file path which was used to generate the hash.
	 * @param string $hash    The hash of the file.
	 *
	 * @return void
	 */
	private function cache($cacheId, $hash) {
		/**
		 * @var HashCacheEntry $hashEntry
		 */
		$hashEntry = HashCacheEntry::findOrGetInstance($cacheId);
		$hashEntry->setHash($hash);
		$hashEntry->save();
	}


	/**
	 * Fetches the hash from the db.
	 * An empty string represents a cache miss and
	 * an none empty string a cache hit.
	 *
	 * @param string $cacheId The cache id which will be used to fetch the stored hash.
	 *
	 * @return string The hexadecimal hash representation or an empty string if a cache miss occurred.
	 */
	private function fetch($cacheId) {
		/**
		 * @var HashCacheEntry $hashEntry
		 */
		$hashEntry = HashCacheEntry::findOrGetInstance($cacheId);
		return $hashEntry->getHash();
	}
}