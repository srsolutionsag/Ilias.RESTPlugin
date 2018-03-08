<?php

namespace RESTController\extensions\ILIASApp\V2\data\block;

use RESTController\extensions\ILIASApp\V2\data\JsonSerializableAware;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/block/BaseBlock.php';

/**
 * Class Picture
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class Picture extends BaseBlock {

	use JsonSerializableAware;

	/**
	 * @var string $title
	 */
	private $title;
	/**
	 * @var string $description
	 */
	private $description;
	/**
	 * @var string $thumbnail
	 */
	private $thumbnail;
	/**
	 * @var string $thumbnailHash
	 */
	private $thumbnailHash;
	/**
	 * @var string $url
	 */
	private $url;
	/**
	 * @var string $hash
	 */
	private $hash;


	/**
	 * Picture constructor.
	 *
	 * @param int    $id
	 * @param int    $sequence
	 * @param string $visibility
	 * @param string $title
	 * @param string $description
	 * @param string $thumbnail
	 * @param string $url
	 * @param string $hash
	 * @param string $thumbnailHash
	 */
	public function __construct($id, $sequence, $visibility, $title, $description, $thumbnail, $url, $hash, $thumbnailHash) {
		parent::__construct($id, $sequence, $visibility);
		$this->title = $title;
		$this->description = $description;
		$this->thumbnail = $thumbnail;
		$this->url = $url;
		$this->hash = $hash;
		$this->thumbnailHash = $thumbnailHash;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @return string
	 */
	public function getThumbnail() {
		return $this->thumbnail;
	}


	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
}