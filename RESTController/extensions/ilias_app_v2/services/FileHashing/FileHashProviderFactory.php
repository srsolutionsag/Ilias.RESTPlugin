<?php

namespace RESTController\extensions\ILIASApp\V2;

require_once __DIR__ . '/SHA256FileHashProvider.php';
require_once __DIR__ . '/FileHashProvider.php';
require_once __DIR__ . '/FileHashDBCacheDecorator.php';

/**
 * Class FileHashProviderFactory
 *
 * Create a preconfigured file hash provider.
 * The main purpose of this factory is to decrease the coupling
 * of the internal implementations to the consumer of the service.
 *
 * @package RESTController\extensions\ILIASApp\V2
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class FileHashProviderFactory {

	/**
	 * Create a file hash provider for the consumer of the file hashing service.
	 *
	 * @return FileHashProvider An file hash provider implementation.
	 */
	public function create() {
		return new FileHashDBCacheDecorator(new SHA256FileHashProvider());
	}

}