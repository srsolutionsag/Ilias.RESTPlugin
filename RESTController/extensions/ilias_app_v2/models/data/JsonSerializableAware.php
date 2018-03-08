<?php

namespace RESTController\extensions\ILIASApp\V2\data;

/**
 * Trait JsonSerializableAware
 *
 * @package RESTController\extensions\ILIASApp\V2\data
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
trait JsonSerializableAware {

	public function jsonSerialize() {
		return get_object_vars($this);
	}
}