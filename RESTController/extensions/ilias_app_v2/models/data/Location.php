<?php

namespace RESTController\extensions\ILIASApp\V2\data;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';

use JsonSerializable;

/**
 * Class Location
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class Location implements JsonSerializable {

	use JsonSerializableAware;

	/**
	 * @var float $latitude
	 */
	private $latitude;
	/**
	 * @var float $longitude
	 */
	private $longitude;
	/**
	 * @var float $elevation
	 */
	private $elevation;
	/**
	 * @var int $radius
	 */
	private $radius;


	/**
	 * Location constructor.
	 *
	 * @param float $latitude
	 * @param float $longitude
	 * @param float $elevation
	 * @param int   $radius
	 */
	public function __construct($latitude, $longitude, $elevation, $radius) {
		$this->latitude = $latitude;
		$this->longitude = $longitude;
		$this->elevation = $elevation;
		$this->radius = $radius;
	}


	/**
	 * @return float
	 */
	public function getLatitude() {
		return $this->latitude;
	}


	/**
	 * @return float
	 */
	public function getLongitude() {
		return $this->longitude;
	}


	/**
	 * @return float
	 */
	public function getElevation() {
		return $this->elevation;
	}


	/**
	 * @return int
	 */
	public function getRadius() {
		return $this->radius;
	}
}