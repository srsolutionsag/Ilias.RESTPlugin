<?php

namespace RESTController\extensions\ILIASApp\V2\data\block;

use JsonSerializable;

/**
 * Class BaseBlock
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
abstract class BaseBlock implements JsonSerializable {

	/**
	 * @var int $sequence
	 */
	protected $sequence;
	/**
	 * @var string $visibility
	 */
	protected $visibility;


	/**
	 * BaseBlock constructor.
	 *
	 * @param int    $sequence
	 * @param string $visibility
	 */
	public function __construct($sequence, $visibility) {
		$this->sequence = $sequence;
		$this->visibility = $visibility;
	}


	/**
	 * @return int
	 */
	public function getSequence() {
		return $this->sequence;
	}


	/**
	 * @return string
	 */
	public function getVisibility() {
		return $this->visibility;
	}
}