<?php

namespace RESTController\extensions\ILIASApp\V2;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/Learnplace.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/Location.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/Map.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/VisitJournalEntry.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/block/Accordion.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/block/IliasLink.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/block/Picture.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/block/Text.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/block/Video.php';

use function count;
use DateTime;
use Generator;
use ilObjUser;
use function intval;
use function is_null;
use function iterator_to_array;
use RESTController\extensions\ILIASApp\V2\data\block\Accordion;
use RESTController\extensions\ILIASApp\V2\data\block\IliasLink;
use RESTController\extensions\ILIASApp\V2\data\block\Picture;
use RESTController\extensions\ILIASApp\V2\data\block\Text;
use RESTController\extensions\ILIASApp\V2\data\block\Video;
use RESTController\extensions\ILIASApp\V2\data\BlockCollection;
use RESTController\extensions\ILIASApp\V2\data\Learnplace;
use RESTController\extensions\ILIASApp\V2\data\Location;
use RESTController\extensions\ILIASApp\V2\data\Map;
use RESTController\extensions\ILIASApp\V2\data\VisitJournalEntry;
use RESTController\libs\RESTilias;
use SplFixedArray;
use SRAG\Learnplaces\container\PluginContainer;
use SRAG\Learnplaces\service\publicapi\block\LearnplaceService;
use SRAG\Learnplaces\service\publicapi\block\VisitJournalService;
use SRAG\Learnplaces\service\publicapi\model\AccordionBlockModel;
use SRAG\Learnplaces\service\publicapi\model\BlockModel;
use SRAG\Learnplaces\service\publicapi\model\ILIASLinkBlockModel;
use SRAG\Learnplaces\service\publicapi\model\LearnplaceModel;
use SRAG\Learnplaces\service\publicapi\model\MapBlockModel;
use SRAG\Learnplaces\service\publicapi\model\PictureBlockModel;
use SRAG\Learnplaces\service\publicapi\model\RichTextBlockModel;
use SRAG\Learnplaces\service\publicapi\model\VideoBlockModel;
use SRAG\Learnplaces\service\publicapi\model\VisitJournalModel;
use SRAG\Learnplaces\util\Visibility;

/**
 * Class LearnplacePlugin
 *
 * @package RESTController\extensions\ILIASApp\V2
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class LearnplacePlugin {

	/**
	 * @var LearnplaceService $learnplaceService
	 */
	private $learnplaceService;
	/**
	 * @var VisitJournalService $visitJournalService
	 */
	private $visitJournalService;


	/**
	 * LearnplacePlugin constructor.
	 */
	public function __construct() {
		$this->learnplaceService = PluginContainer::resolve(LearnplaceService::class);
		$this->visitJournalService = PluginContainer::resolve(VisitJournalService::class);
	}


	/**
	 * Fetches the corresponding learnplace by object id.
	 *
	 * @param int $objectId The object id of the learnplace which should be fetched.
	 *
	 * @return Learnplace   The learnplace with the given object id.
	 */
	public function fetchByObjectId($objectId) {
		$learnplace = $this->learnplaceService->findByObjectId($objectId);
		$location = $learnplace->getLocation();
		$map = new Map($this->getMapVisibility($learnplace));
		$mappedLocation = new Location(
			$location->getLatitude(),
			$location->getLongitude(),
			$location->getElevation(),
			$location->getRadius()
		);
		$mappedLearnplace = new Learnplace($learnplace->getObjectId(), $mappedLocation, $map);
		return $mappedLearnplace;
	}


	/**
	 * Registers the visit of the current user.
	 *
	 * @param int $objectId     The object id of the learnplace which got visited by the user.
	 * @param int $timestamp    The timestmap of the visit.
	 *
	 * @return void
	 */
	public function visitLearnplace($objectId, $timestamp) {
		/**
		 * @var ilObjUser $currentUser
		 */
		$userId = intval(RESTilias::loadIlUser()->getId()); //fetch current user.

		$time = new DateTime();
		$time->setTimestamp($timestamp);

		$visits = $this->visitJournalService->findByObjectId($objectId);
		$visit = $this->fetchVisitByUserId($visits, $userId);
		if(is_null($visit)) {
			$visitJournalModel = new VisitJournalModel();
			$visitJournalModel
				->setTime($time)
				->setUserId($userId);
			$visitJournalModel = $this->visitJournalService->store($visitJournalModel);

			//save the relation of the visit journal --> learnplace
			$visits[] = $visitJournalModel;
			$learnplace = $this->learnplaceService->findByObjectId($objectId);
			$learnplace->setVisitJournals($visits);
			$this->learnplaceService->store($learnplace);
		}
		else {
			$visit->setTime($time);
			$this->visitJournalService->store($visit);
		}
	}


	/**
	 * @param VisitJournalModel[] $visits
	 * @param int                 $userId
	 *
	 * @return VisitJournalModel|null
	 */
	private function fetchVisitByUserId(array $visits, $userId) {
		foreach ($visits as $visit) {
			if($visit->getUserId() === $userId)
				return $visit;
		}

		return NULL;
	}


	/**
	 * @param int $objectId
	 *
	 * @return BlockCollection
	 */
	public function fetchBlocks($objectId) {
		$learnplace = $this->learnplaceService->findByObjectId($objectId);
		$collection = new BlockCollection(
			iterator_to_array($this->fetchTextBlocks($learnplace->getBlocks())),
			iterator_to_array($this->fetchPictureBlocks($learnplace->getBlocks())),
			iterator_to_array($this->fetchVideoBlocks($learnplace->getBlocks())),
			iterator_to_array($this->fetchIliasLinkBlocks($learnplace->getBlocks())),
			iterator_to_array($this->fetchAccordionkBlocks($learnplace->getBlocks()))
		);
		return $collection;
	}


	/**
	 * @param BlockModel[] $blocks
	 * @return Generator<Text>
	 */
	private function fetchTextBlocks(array $blocks) {
		foreach ($blocks as $block) {
			if($block instanceof RichTextBlockModel) {
				$text = new Text($block->getSequence(), $block->getVisibility(), $block->getContent());
				yield $text;
			}
		}
	}

	/**
	 * @param BlockModel[] $blocks
	 * @return Generator<Picture>
	 */
	private function fetchPictureBlocks(array $blocks) {
		foreach ($blocks as $block) {
			if($block instanceof PictureBlockModel) {
				$picture = new Picture($block->getSequence(), $block->getVisibility(), $block->getTitle(), $block->getDescription(), $block->getPicture()->getPreviewPath(), $block->getPicture()->getOriginalPath());
				yield $picture;
			}
		}
	}

	/**
	 * @param BlockModel[] $blocks
	 * @return Generator<Video>
	 */
	private function fetchVideoBlocks(array $blocks) {
		foreach ($blocks as $block) {
			if($block instanceof VideoBlockModel) {
				$video = new Video($block->getSequence(), $block->getVisibility(), $block->getPath());
				yield $video;
			}
		}
	}

	/**
	 * @param BlockModel[] $blocks
	 * @return Generator<IliasLink>
	 */
	private function fetchIliasLinkBlocks(array $blocks) {
		foreach ($blocks as $block) {
			if($block instanceof ILIASLinkBlockModel) {
				$iliasLink = new IliasLink($block->getSequence(), $block->getVisibility(), $block->getRefId());
				yield $iliasLink;
			}
		}
	}

	/**
	 * @param BlockModel[] $blocks
	 * @return Generator<IliasLink>
	 */
	private function fetchAccordionkBlocks(array $blocks) {
		foreach ($blocks as $block) {
			if($block instanceof AccordionBlockModel) {
				$accordion = new Accordion(
					$block->getSequence(),
					$block->getVisibility(),
					iterator_to_array($this->fetchTextBlocks($block->getBlocks())),
					iterator_to_array($this->fetchPictureBlocks($block->getBlocks())),
					iterator_to_array($this->fetchVideoBlocks($block->getBlocks())),
					iterator_to_array($this->fetchIliasLinkBlocks($block->getBlocks()))
					);
				yield $accordion;
			}
		}
	}


	/**
	 * Fetches the visit journal of a learnplace by object id.
	 *
	 * @param int $objectId             The object id of the learnplace which should be fetched.
	 *
	 * @return \SplFixedArray (VisitJournalEntry[])      All visit journal entries of the learnplace with the given object id.
	 */
	public function fetchVisitJournal($objectId) {
		$rawJournalEntries = $this->visitJournalService->findByObjectId($objectId);
		$mapped = new SplFixedArray(count($rawJournalEntries));
		$index = 0;
		foreach ($rawJournalEntries as $entry) {
			$mapped[$index++] = $this->mapJournalEntry($entry);
		}

		return $mapped;
	}


	/**
	 * @param VisitJournalModel $journalEntry
	 *
	 * @return VisitJournalEntry
	 */
	private function mapJournalEntry(VisitJournalModel $journalEntry) {
		return new VisitJournalEntry(
			$journalEntry->getUserId(),
			$journalEntry->getTime()->getTimestamp()
		);
	}

	private function getMapVisibility(LearnplaceModel $learnplace) {
		foreach ($learnplace->getBlocks() as $block) {
			if($block instanceof MapBlockModel)
				return $block->getVisibility();
		}
		return Visibility::NEVER;
	}
}