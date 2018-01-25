<?php

namespace RESTController\extensions\ILIASApp\V2\data\block;

use RESTController\extensions\ILIASApp\V2\data\JsonSerializableAware;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/block/BaseBlock.php';

/**
 * Class Text
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class Text extends BaseBlock {

	use JsonSerializableAware;

	/**
	 * @var string $content
	 */
	private $content;


	/**
	 * Text constructor.
	 *
	 * @param int       $sequence
	 * @param int       $visibility
	 * @param string    $content
	 */
	public function __construct($sequence, $visibility, $content) {
		parent::__construct($sequence, $visibility);
		$this->content = $content;
	}
}