<?php

namespace RESTController\extensions\ILIASApp\V2\data;

require_once __DIR__ . '/JsonSerializableAware.php';

/**
 * Class IliasTreeItem
 *
 * An ILIAS tree item contains all app relevant data about an ILIAS tree element / child.
 *
 * @package RESTController\extensions\ILIASApp\V2\data
 * @author Nicolas Schaefli <ns@studer-raimann.ch>
 * @version 1.0.0
 */
final class IliasTreeItem implements \JsonSerializable
{
    use JsonSerializableAware;

    /**
     * @var string $objId
     */
    private $objId;
    /**
     * @var string $title
     */
    private $title;
    /**
     * @var string $description
     */
    private $description;
    /**
     * @var bool $hasPageLayout
     */
    private $hasPageLayout;
    /**
     * @var bool $hasTimeline
     */
    private $hasTimeline;
    /**
     * @var string $permissionType
     */
    private $permissionType;
    /**
     * @var string $refId
     */
    private $refId;
    /**
     * @var string $parentRefId
     */
    private $parentRefId;
    /**
     * @var string $type
     */
    private $type;
    /**
     * @var string $link
     */
    private $link;
    /**
     * @var string[] $repoPath
     */
    private $repoPath;

    /**
     * IliasTreeItem constructor.
     * @param string $objId
     * @param string $title
     * @param string $description
     * @param bool $hasPageLayout
     * @param bool $hasTimeline
     * @param string $permissionType
     * @param string $refId
     * @param string $parentRefId
     * @param string $type
     * @param string $link
     * @param string[] $repoPath
     */
    public function __construct($objId, $title, $description, $hasPageLayout, $hasTimeline, $permissionType, $refId, $parentRefId, $type, $link, array $repoPath)
    {
        $this->objId = $objId;
        $this->title = $title;
        $this->description = $description;
        $this->hasPageLayout = $hasPageLayout;
        $this->hasTimeline = $hasTimeline;
        $this->permissionType = $permissionType;
        $this->refId = $refId;
        $this->parentRefId = $parentRefId;
        $this->type = $type;
        $this->link = $link;
        $this->repoPath = $repoPath;
    }

    /**
     * @return string
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isHasPageLayout()
    {
        return $this->hasPageLayout;
    }

    /**
     * @return bool
     */
    public function isHasTimeline()
    {
        return $this->hasTimeline;
    }

    /**
     * @return string
     */
    public function getPermissionType()
    {
        return $this->permissionType;
    }

    /**
     * @return string
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * @return string
     */
    public function getParentRefId()
    {
        return $this->parentRefId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return string[]
     */
    public function getRepoPath()
    {
        return $this->repoPath;
    }

    /**
     * @param string $objId
     * @return IliasTreeItem
     */
    public function setObjId($objId)
    {
        $clone = clone $this;
        $clone->objId = $objId;
        return $this;
    }

    /**
     * @param string $title
     * @return IliasTreeItem
     */
    public function setTitle($title)
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    /**
     * @param string $description
     * @return IliasTreeItem
     */
    public function setDescription($description)
    {
        $clone = clone $this;
        $clone->description = $description;
        return $clone;
    }

    /**
     * @param bool $hasPageLayout
     * @return IliasTreeItem
     */
    public function setHasPageLayout($hasPageLayout)
    {
        $clone = clone $this;
        $clone->hasPageLayout = $hasPageLayout;
        return $clone;
    }

    /**
     * @param bool $hasTimeline
     * @return IliasTreeItem
     */
    public function setHasTimeline($hasTimeline)
    {
        $clone = clone $this;
        $clone->hasTimeline = $hasTimeline;
        return $clone;
    }

    /**
     * @param string $permissionType
     * @return IliasTreeItem
     */
    public function setPermissionType($permissionType)
    {
        $clone = clone $this;
        $clone->permissionType = $permissionType;
        return $clone;
    }

    /**
     * @param string $refId
     * @return IliasTreeItem
     */
    public function setRefId($refId)
    {
        $clone = clone $this;
        $clone->refId = $refId;
        return $clone;
    }

    /**
     * @param string $parentRefId
     * @return IliasTreeItem
     */
    public function setParentRefId($parentRefId)
    {
        $clone = clone $this;
        $clone->parentRefId = $parentRefId;
        return $clone;
    }

    /**
     * @param string $type
     * @return IliasTreeItem
     */
    public function setType($type)
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    /**
     * @param string $link
     * @return IliasTreeItem
     */
    public function setLink($link)
    {
        $clone = clone $this;
        $clone->link = $link;
        return $clone;
    }

    /**
     * @param string[] $repoPath
     * @return IliasTreeItem
     */
    public function setRepoPath($repoPath)
    {
        $clone = clone $this;
        $clone->repoPath = $repoPath;
        return $clone;
    }
}