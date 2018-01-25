<?php

namespace RESTController\extensions\ILIASApp\V2\data;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';

use JsonSerializable;

/**
 * Class VisitJournalEntry
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class VisitJournalEntry implements JsonSerializable {

	use JsonSerializableAware;

	/**
	 * @var int $username
	 */
	private $userId;

	/**
	 * @var int $timestamp
	 */
	private $timestamp;


	/**
	 * VisitJournalEntry constructor.
	 *
	 * @param int $userId
	 * @param int $timestamp
	 */
	public function __construct($userId, $timestamp) {
		$this->userId = $userId;
		$this->timestamp = $timestamp;
	}


	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->userId;
	}


	/**
	 * @return int
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}
}