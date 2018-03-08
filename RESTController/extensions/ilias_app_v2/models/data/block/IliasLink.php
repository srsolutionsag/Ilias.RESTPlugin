<?php

namespace RESTController\extensions\ILIASApp\V2\data\block;

use RESTController\extensions\ILIASApp\V2\data\JsonSerializableAware;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/block/BaseBlock.php';

/**
 * Class IliasLink
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class IliasLink extends BaseBlock {

	use JsonSerializableAware;

	/**
	 * @var int $refId
	 */
	private $refId;


	/**
	 * IliasLink constructor.
	 *
	 * @param int       $id
	 * @param int       $sequence
	 * @param string    $visibility
	 * @param int       $refId
	 */
	public function __construct($id, $sequence, $visibility, $refId) {
		parent::__construct($id, $sequence, $visibility);
		$this->refId = $refId;
	}


	/**
	 * @return int
	 */
	public function getRefId() {
		return $this->refId;
	}
}