<?php

namespace RESTController\extensions\ILIASApp\V2;

/**
 * Interface FileHashProvider
 *
 * The file hash provider provides functions to hash a
 * file on the filesystem.
 *
 * The type of the hash as well as the caching mechanics are considered
 * an implementation detail.
 *
 * @package RESTController\extensions\ILIASApp\V2
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
interface FileHashProvider {

	/**
	 * Computes a hash of the given file.
	 *
	 * @param string $filePath The file which should be hashed.
	 *
	 * @return string The computed hash in hexadecimal.
	 */
	public function hash($filePath);
}