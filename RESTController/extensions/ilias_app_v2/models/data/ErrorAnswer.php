<?php

namespace RESTController\extensions\ILIASApp\V2\data;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';

use JsonSerializable;

/**
 * Class ErrorAnswer
 *
 * @package RESTController\extensions\ILIASApp\V2\data
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class ErrorAnswer implements JsonSerializable {

	use JsonSerializableAware;

	/**
	 * @var string $reason
	 */
	private $reason;


	/**
	 * ErrorAnswer constructor.
	 *
	 * @param string $reason
	 */
	public function __construct($reason) { $this->reason = $reason; }


	/**
	 * @return string
	 */
	public function getReason() {
		return $this->reason;
	}
}