<?php

/**
 * Class ErrorMessage
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class ErrorMessage implements JsonSerializable {

	/**
	 * @var string $message
	 */
	private $message;


	/**
	 * ErrorMessage constructor.
	 *
	 * @param string $message
	 */
	public function __construct($message) { $this->message = $message; }


	/**
	 * @inheritDoc
	 */
	public function jsonSerialize() {
		return get_object_vars($this);
	}
}