<?php

namespace RESTController\extensions\ILIASApp\V2\data\block;

use RESTController\extensions\ILIASApp\V2\data\JsonSerializableAware;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/block/BaseBlock.php';

/**
 * Class Video
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class Video extends BaseBlock {

	use JsonSerializableAware;

	/**
	 * @var string $url
	 */
	private $url;


	/**
	 * Video constructor.
	 *
	 * @param        $sequence
	 * @param        $visibility
	 * @param string $url
	 */
	public function __construct($sequence, $visibility, $url) {
		parent::__construct($sequence, $visibility);
		$this->url = $url;
	}
}