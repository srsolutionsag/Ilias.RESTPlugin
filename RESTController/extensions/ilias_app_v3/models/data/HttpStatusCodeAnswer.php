<?php

namespace RESTController\extensions\ILIASApp\V2\data;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';

use JsonSerializable;

/**
 * Class ErrorAnswer
 *
 * @package RESTController\extensions\ILIASApp\V3\data
 *
 * @author  Marc Schneiter <msc@studer-raimann.ch>
 */
final class HttpStatusCodeAnswer implements JsonSerializable {

	use JsonSerializableAware;

	/**
	 * @var string $cause
	 */
	private $cause;


	/**
	 * HttpStatusCodeAnswer constructor.
	 *
	 * @param string $cause
	 */
	public function __construct($cause) { $this->cause = $cause; }


	/**
	 * @return string
	 */
	public function getCause() {
		return $this->cause;
	}
}