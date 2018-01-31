<?php

namespace RESTController\extensions\ILIASApp\V2\data\block;

require_once './Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/RESTController/extensions/ilias_app_v2/models/data/JsonSerializableAware.php';

use JsonSerializable;
use RESTController\extensions\ILIASApp\V2\data\JsonSerializableAware;

/**
 * Class NewsItem
 *
 * @package RESTController\extensions\ILIASApp\V2\data\block
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class NewsItem implements JsonSerializable {

	use JsonSerializableAware;
	/**
	 * @var int $newsId
	 */
	private $newsId = 0;
	/**
	 * @var int $newsContext
	 */
	private $newsContext = 0;
	/**
	 * @var int $refId
	 */
	private $refId = 0;
	/**
	 * @var bool $userRead
	 */
	private $userRead = false;
	/**
	 * @var string $title
	 */
	private $title = '';
	/**
	 * @var string $subtitle
	 */
	private $subtitle = '';
	/**
	 * @var string $content
	 */
	private $content = '';
	/**
	 * @var int $createDate
	 */
	private $createDate = 0;
	/**
	 * @var int $updateDate
	 */
	private $updateDate = 0;


	/**
	 * @return int
	 */
	public function getNewsId() {
		return $this->newsId;
	}


	/**
	 * @param int $newsId
	 *
	 * @return NewsItem
	 */
	public function setNewsId($newsId) {
		$this->newsId = $newsId;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getNewsContext() {
		return $this->newsContext;
	}


	/**
	 * @param int $newsContext
	 *
	 * @return NewsItem
	 */
	public function setNewsContext($newsContext) {
		$this->newsContext = $newsContext;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getRefId() {
		return $this->refId;
	}


	/**
	 * @param int $refId
	 *
	 * @return NewsItem
	 */
	public function setRefId($refId) {
		$this->refId = $refId;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function isUserRead() {
		return $this->userRead;
	}


	/**
	 * @param bool $userRead
	 *
	 * @return NewsItem
	 */
	public function setUserRead($userRead) {
		$this->userRead = $userRead;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param string $title
	 *
	 * @return NewsItem
	 */
	public function setTitle($title) {
		$this->title = $title;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getSubtitle() {
		return $this->subtitle;
	}


	/**
	 * @param string $subtitle
	 *
	 * @return NewsItem
	 */
	public function setSubtitle($subtitle) {
		$this->subtitle = $subtitle;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}


	/**
	 * @param string $content
	 *
	 * @return NewsItem
	 */
	public function setContent($content) {
		$this->content = $content;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getCreateDate() {
		return $this->createDate;
	}


	/**
	 * @param int $createDate
	 *
	 * @return NewsItem
	 */
	public function setCreateDate($createDate) {
		$this->createDate = $createDate;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getUpdateDate() {
		return $this->updateDate;
	}


	/**
	 * @param int $updateDate
	 *
	 * @return NewsItem
	 */
	public function setUpdateDate($updateDate) {
		$this->updateDate = $updateDate;

		return $this;
	}
}