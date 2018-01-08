<?php

namespace RESTController\extensions\ILIASApp\V2\data\block;

use RESTController\extensions\ILIASApp\V2\data\JsonSerializableAware;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/block/BaseBlock.php';
/**
 * Class Accordion
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class Accordion extends BaseBlock {

	use JsonSerializableAware;

	/**
	 * @var Text[] $text
	 */
	private $text;
	/**
	 * @var Picture[] $picture
	 */
	private $picture;
	/**
	 * @var Video[] $video
	 */
	private $video;
	/**
	 * @var IliasLink[] $iliasLink
	 */
	private $iliasLink;


	/**
	 * Accordion constructor.
	 *
	 * @param             $sequence
	 * @param             $visibility
	 * @param Text[]      $text
	 * @param Picture[]   $picture
	 * @param Video[]     $video
	 * @param IliasLink[] $iliasLink
	 */
	public function __construct($sequence, $visibility, array $text, array $picture, array $video, array $iliasLink) {
		parent::__construct($sequence, $visibility);
		$this->text = $text;
		$this->picture = $picture;
		$this->video = $video;
		$this->iliasLink = $iliasLink;
	}


	/**
	 * @return Text[]
	 */
	public function getText() {
		return $this->text;
	}


	/**
	 * @return Picture[]
	 */
	public function getPicture() {
		return $this->picture;
	}


	/**
	 * @return Video[]
	 */
	public function getVideo() {
		return $this->video;
	}


	/**
	 * @return IliasLink[]
	 */
	public function getIliasLink() {
		return $this->iliasLink;
	}
}