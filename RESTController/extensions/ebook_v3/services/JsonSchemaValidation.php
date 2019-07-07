<?php

namespace RESTController\extensions\eBook\v3\services;

use Swaggest\JsonSchema\Schema;

/**
 * Class JsonSchemaValidation
 *
 * Validate predefined requests.
 * See schemas folder in order to inspect the json schemas.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class JsonSchemaValidation {

	/**
	 * Validates the ebook sync request with the schema SyncSchema.json.
	 *
	 * @param $request
	 *
	 * @throws \Swaggest\JsonSchema\Exception
	 * @throws \Swaggest\JsonSchema\InvalidValue
	 *
	 * @return void
	 */
	public static function validateSyncRequest($request) {

		// must be required because Schema::import will leak the full file path if a json schema is loaded from the filesystem.
		require_once __DIR__ . '/schemas/SyncSchema.php';

		/**
		 * @var string $schema
		 */
		$jsonSchema = Schema::import(json_decode($schema));
		$jsonSchema->in($request);
	}
}