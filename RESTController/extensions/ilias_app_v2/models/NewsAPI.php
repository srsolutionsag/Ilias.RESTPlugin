<?php

namespace RESTController\extensions\ILIASApp\V2;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/libs/RESTilias.php';
require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/NewsItem.php';

use DateTime;
use ilLanguage;
use ilNewsItem;
use ilObject;
use ilObjectDefinition;
use ilObjectPlugin;
use function preg_match;
use RESTController\extensions\ILIASApp\V2\data\block\NewsItem;
use RESTController\libs\RESTilias;

/**
 * Class NewsAPI
 *
 * The news api class wraps the ilNewsItem api and parses
 * the old data in a more suitable format for the Pegasus app.
 *
 * @package RESTController\extensions\ILIASApp\V2
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class NewsAPI {

	/**
	 * @var ilLanguage $language
	 */
	private $language;
	/**
	 * @var ilObjectDefinition $definition
	 */
	private $definition;


	/**
	 * NewsAPI constructor.
	 *
	 * @param ilLanguage         $language
	 * @param ilObjectDefinition $definition
	 */
	public function __construct(ilLanguage $language, ilObjectDefinition $definition) {

		//the news lang module is not loaded by the REST plugin
		$newsLangModuleName = 'news';
		if(!in_array($newsLangModuleName, $language->getUsedModules()))
			$language->loadLanguageModule('news');

		$this->language = $language;
		$this->definition = $definition;
	}


	/**
	 * Find all news entries for the currently authenticated user.
	 * The found news are parsed to NewsItem instances.
	 *
	 * @return NewsItem[] All news found news of the current user.
	 *
	 * @see NewsItem
	 */
	public function findAllNewsForAuthenticatedUser() {
		$userId = intval(RESTilias::loadIlUser()->getId()); //fetch current user.
		$onlyPublic = false;
		$preventAggregation = false;

		$period = ilNewsItem::_lookupUserPDPeriod($userId);
		$rawNewsEntries = ilNewsItem::_getNewsItemsOfUser($userId, $onlyPublic, $preventAggregation, $period);
		$newsEntries = [];

		foreach ($rawNewsEntries as $rawNewsEntry) {

			// determine the actual content and title
			$obj_id = intval(ilObject::_lookupObjId($rawNewsEntry['ref_id']));

			$rawObjectType = strval(ilObject::_lookupType($obj_id));

			$obj_type = $this->translateObjectType($rawObjectType);
			$obj_title = strval(ilObject::_lookupTitle($obj_id));
			$title = sprintf('%s: %s', $obj_type, $obj_title);
			$content = ilNewsItem::determineNewsContent($rawNewsEntry["context_obj_type"], $rawNewsEntry["content"], $rawNewsEntry["content_text_is_lang_var"]);
			$subtitle = $this->translateSubtitle($rawNewsEntry);

			$newsEntry = new NewsItem();
			$newsEntry
				->setNewsId(intval($rawNewsEntry['id']))
				->setNewsContext((key_exists('agg_ref_id', $rawNewsEntry)) ? intval($rawNewsEntry['agg_ref_id']) : intval($rawNewsEntry['ref_id']))
				->setTitle($title)
				->setSubtitle((is_null($subtitle)) ? '' : strval($subtitle))
				->setContent((is_null($content)) ? '' : strval($content))
				->setCreateDate((new DateTime($rawNewsEntry['creation_date']))->getTimestamp())
				->setUpdateDate((new DateTime($rawNewsEntry['update_date']))->getTimestamp());
			$newsEntries[] = $newsEntry;
		}


		// Sorts the values ascending by news id.
		usort($newsEntries, function($a, $b) {
			/**
			 * @var NewsItem $a
			 * @var NewsItem $b
			 */
			return $a->getNewsId() - $b->getNewsId();
		});
		
		return $newsEntries;
	}


	/**
	 * Translates an object type identifier, to human readable text.
	 * For example: xsrl -> learnplace
	 *
	 * @param string $objectType    The object type identifier which should be translated.
	 * @return string               The translated type. xsrl -> learnplace
	 */
	private function translateObjectType($objectType) {
		$obj_type = null;

		if (!$this->definition->isPlugin($objectType))
		{
			$obj_type = $this->language->txt("obj_$objectType");
		}
		else
		{
			include_once("./Services/Component/classes/class.ilPlugin.php");
			$obj_type = ilObjectPlugin::lookupTxtById($objectType, "obj_$objectType");
		}

		return strval($obj_type);
	}


	/**
	 * Translates the news title.
	 *
	 * @param string[] $rawNewsEntry The raw news entry.
	 *
	 * @return string The translated news title.
	 */
	private function translateSubtitle(array $rawNewsEntry) {
		$subtitle = ilNewsItem::determineNewsTitle(
			$rawNewsEntry["context_obj_type"],
			$rawNewsEntry["title"],
			$rawNewsEntry["content_is_lang_var"],
			(array_key_exists('agg_ref_id', $rawNewsEntry)) ? $rawNewsEntry['agg_ref_id'] : NULL,
			(array_key_exists('aggregation', $rawNewsEntry)) ? $rawNewsEntry['aggregation'] : NULL
		);

		$matches = [];
		if(preg_match('/^-(.*?)-$/', $subtitle,$matches) === 1 && $this->definition->isPlugin($rawNewsEntry["context_obj_type"])) {
			//fallback if the news service failed to translate a plugin title (BUG ILIAS 5.2.12)
			return ilObjectPlugin::lookupTxtById($rawNewsEntry["context_obj_type"], $matches[1]);
		}

		return$subtitle;
	}
}