<?php

namespace RESTController\extensions\ILIASApp\V2\data;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';

use JsonSerializable;
use SRAG\Learnplaces\util\Visibility;

/**
 * Class Map
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class Map implements JsonSerializable {

	use JsonSerializableAware;

	/**
	 * @var string $visibility
	 *
	 * @see Visibility available constants
	 */
	private $visibility = "";
	/**
	 * Zoom level between 0 and 18
	 *
	 * @var int $zoomLevel
	 */
	private $zoomLevel = 0;


	/**
	 * Map constructor.
	 *
	 * @param string $visibility
	 * @param int    $zoomLevel
	 */
	public function __construct($visibility, $zoomLevel) {
		$this->visibility = $visibility;
		$this->zoomLevel = $zoomLevel;
	}


	/**
	 * @return string
	 */
	public function getVisibility() {
		return $this->visibility;
	}


	/**
	 * @return int
	 */
	public function getZoomLevel() {
		return $this->zoomLevel;
	}
}