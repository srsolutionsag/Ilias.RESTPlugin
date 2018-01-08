<?php

namespace RESTController\extensions\ILIASApp\V2\data;

use JsonSerializable;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/Location.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/Map.php';

/**
 * Class Learnplace
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class Learnplace implements JsonSerializable {

	use JsonSerializableAware;

	/**
	 * @var string $objectId
	 */
	private $objectId;
	/**
	 * @var Location $location
	 */
	private $location;
	/**
	 * @var Map $map
	 */
	private $map;


	/**
	 * Learnplace constructor.
	 *
	 * @param string   $objectId
	 * @param Location $location
	 * @param Map      $map
	 */
	public function __construct($objectId, Location $location, Map $map) {
		$this->objectId = $objectId;
		$this->location = $location;
		$this->map = $map;
	}


	/**
	 * @return string
	 */
	public function getObjectId() {
		return $this->objectId;
	}


	/**
	 * @return Location
	 */
	public function getLocation() {
		return $this->location;
	}


	/**
	 * @return Map
	 */
	public function getMap() {
		return $this->map;
	}
}