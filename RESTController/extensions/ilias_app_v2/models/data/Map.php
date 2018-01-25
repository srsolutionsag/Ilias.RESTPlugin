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
	private $visibility;


	/**
	 * Map constructor.
	 *
	 * @param string $visibility
	 */
	public function __construct($visibility) { $this->visibility = $visibility; }


	/**
	 * @return string
	 */
	public function getVisibility() {
		return $this->visibility;
	}
}