<?php

namespace RESTController\extensions\ILIASApp\V2;

use function hash;
use ilException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use SRAG\Learnplaces\container\PluginContainer;

/**
 * Class SHA256FileHashProvider
 *
 * Generates sha256 hashes of files on the filesystem.
 *
 * @package RESTController\extensions\ILIASApp\V2
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class SHA256FileHashProvider implements FileHashProvider {

	/**
	 * @var FilesystemInterface $filesystem
	 */
	private $filesystem;


	/**
	 * SHA256FileHashProvider constructor.
	 */
	public function __construct() {
		$this->filesystem = PluginContainer::resolve(FilesystemInterface::class);
	}


	/**
	 * Computes the sha256 hash of the given file.
	 *
	 * @param string $filePath  The path to the file which should be used for the hash computation.
	 *
	 * @return string Sha256 hash of the given file content.
	 *
	 * @throws FileNotFoundException    Thrown if the file was not found.
	 * @throws ilException              Thrown if no sha256 hash could be calculated.
	 */
	public function hash($filePath) {
		$hash = hash('sha256', $this->filesystem->read($filePath));
		if($hash === false)
			throw new ilException("Unable to sha256 hash the given file \"$filePath\".");
		return $hash;
	}
}