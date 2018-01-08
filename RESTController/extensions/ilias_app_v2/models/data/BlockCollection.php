<?php

namespace RESTController\extensions\ILIASApp\V2\data;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';

use JsonSerializable;
use RESTController\extensions\ILIASApp\V2\data\block\Accordion;
use RESTController\extensions\ILIASApp\V2\data\block\IliasLink;
use RESTController\extensions\ILIASApp\V2\data\block\Picture;
use RESTController\extensions\ILIASApp\V2\data\block\Text;
use RESTController\extensions\ILIASApp\V2\data\block\Video;

/**
 * Class BlockCollection
 *
 * @package RESTController\extensions\ILIASApp\V2\data
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class BlockCollection implements JsonSerializable {

	use JsonSerializableAware;
	/**
	 * @var Text[] $text
	 */
	private $text = [];
	/**
	 * @var Picture[] $picture
	 */
	private $picture = [];
	/**
	 * @var Video[] $video
	 */
	private $video = [];
	/**
	 * @var IliasLink[] $iliasLink
	 */
	private $iliasLink = [];
	/**
	 * @var Accordion[] $accordion
	 */
	private $accordion = [];


	/**
	 * BlockCollection constructor.
	 *
	 * @param Text[]      $text
	 * @param Picture[]   $picture
	 * @param Video[]     $video
	 * @param IliasLink[] $iliasLink
	 * @param Accordion[] $accordion
	 */
	public function __construct(array $text, array $picture, array $video, array $iliasLink, array $accordion) {
		$this->text = $text;
		$this->picture = $picture;
		$this->video = $video;
		$this->iliasLink = $iliasLink;
		$this->accordion = $accordion;
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


	/**
	 * @return Accordion[]
	 */
	public function getAccordion() {
		return $this->accordion;
	}
}