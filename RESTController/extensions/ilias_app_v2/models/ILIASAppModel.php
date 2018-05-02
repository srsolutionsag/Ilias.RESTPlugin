<?php namespace RESTController\extensions\ILIASApp\V2;

use ilSessionAppointment;
use RESTController\extensions\ILIASApp\V2\data\IliasTreeItem;
use \RESTController\libs as Libs;

require_once('./Services/Membership/classes/class.ilParticipants.php');
require_once('./Modules/File/classes/class.ilObjFile.php');
require_once('./Services/Link/classes/class.ilLink.php');
require_once('./Services/Administration/classes/class.ilSetting.php');
require_once __DIR__ . '/data/IliasTreeItem.php';


final class ILIASAppModel extends Libs\RESTModel
{

	/**
	 * @var \ilDB
	 */
	protected $db;

	/**
	 * @var \ilAccessHandler
	 */
	protected $access;


	public function __construct()
	{
		global $ilDB, $ilAccess;
		Libs\RESTilias::loadIlUser();
		$this->db = $ilDB;
		$this->access = $ilAccess;
	}


	/**
	 * Creates and saves a token for the passed in {@code $userId}.
	 * The token has a very short life time, because it can be used
	 * to log into ILIAS without username and password.
	 *
	 * If the token with the associated user id exists already,
	 * it will be returned and no token will be generated.
	 * The expire date of the token will NOT be updated.
	 *
	 * @param $userId int the user to create the token for
	 *
	 * @return string the created token or the stored token if it exists already
	 */
	public function createToken($userId) {

		// Return the token if the user has already one associated
		$sql = "SELECT * FROM ui_uihk_rest_token WHERE user_id = ". $this->db->quote($userId, 'integer');
		$set = $this->db->query($sql);

		$token = $this->db->fetchAssoc($set);

		if (
			is_array($token) &&
			array_key_exists("token", $token)) {
			return $token['token'];
		}

		// Create a new token and associate it with the user id
		$token = hash("sha512", rand(100, 10000) * 17 + $userId); // hash with the user id
		$expires = date("Y-m-d H:i:s", time() + 60); // token is 1 min valid

		$fields = array(
			"user_id" => array("integer", $userId),
			"token" => array("text", $token),
			"expires" => array("timestamp", $expires)
		);

		$this->db->insert("ui_uihk_rest_token", $fields);

		return $token;
	}

	/**
	 * Return courses and groups from desktop
	 *
	 * @param int $userId
	 * @return array
	 */
	public function getDesktopData($userId)
	{
		return $this->fetchObjectData(\ilParticipants::_getMembershipByType($userId, array('crs', 'grp')));
	}


	/**
	 * Return objects in tree under the given $refId.
	 *
	 * @param int $refId
	 * @param int $userId
	 * @return array
	 */
	public function getChildren($refId, $userId)
	{
		$sql = 'SELECT object_reference.obj_id FROM tree 
                INNER JOIN object_reference ON (object_reference.ref_id = tree.child AND object_reference.deleted IS NULL)
                WHERE parent = ' . $this->db->quote($refId, 'integer');
		$set = $this->db->query($sql);
		$objIds = array();
		while ($row = $this->db->fetchObject($set)) {
			$objIds[] = $row->obj_id;
		}

		return $this->fetchObjectData($objIds);
	}


	public function getFileData($refId, $userId)
	{
		$file = new \ilObjFile($refId);
		$fileName = mb_strtolower($file->getFileName());
		$fileName = preg_replace('/[^a-z0-9\-_\.]+/', '', $fileName);

		return array(
			'fileExtension' => $file->getFileExtension(),
			'fileName' => $fileName,
			'fileSize' => $file->getFileSize(),
			'fileType' => $file->getFileType(),
			'fileVersion' => $file->getVersion(),
			'fileVersionDate' => $file->getLastUpdateDate(),
		);
	}


	public function getChildrenRecursive($refId, $userId)
	{
		if ($this->isNestedSet())
			return $this->getChildrenRecursiveOnNestedSet($refId, $userId);
		else {
			return $this->getChildrenRecursiveOnMaterializedPath($refId, $userId);
		}

	}


	private function getChildrenRecursiveOnMaterializedPath($refId, $userId)
	{
		$sql = "SELECT object_reference.obj_id FROM tree AS parent
                INNER JOIN tree AS child ON child.path LIKE CONCAT(parent.path, '.%')
                INNER JOIN object_reference on child.child = object_reference.ref_id
                WHERE parent.child = " . $this->db->quote($refId, 'integer');
		$set = $this->db->query($sql);
		$objIds = array();
		while ($row = $this->db->fetchObject($set)) {
			$objIds[] = $row->obj_id;
		}

		return $this->fetchObjectData($objIds);
	}


	private function getChildrenRecursiveOnNestedSet($refId, $userId)
	{
		$sql = 'SELECT object_reference.obj_id FROM tree
                INNER JOIN tree AS tree_children ON (tree_children.lft > tree.lft AND tree_children.rgt < tree.rgt)
                INNER JOIN object_reference ON (object_reference.ref_id = tree_children.child AND object_reference.deleted IS NULL)
                WHERE tree.child = ' . $this->db->quote($refId, 'integer');
		$set = $this->db->query($sql);
		$objIds = array();
		while ($row = $this->db->fetchObject($set)) {
			$objIds[] = $row->obj_id;
		}

		return $this->fetchObjectData($objIds);
	}


	private function isNestedSet()
	{
		$query = "SELECT * FROM settings WHERE keyword LIKE 'main_tree_impl'";
		$set = $this->db->query($query);
		$setting = $this->db->fetchAssoc($set);
		//if nothing is set, then it's a nested set.
		if (!$setting) {
			return true;
		} else {
			return $setting['value'] == 'ns';
		}
	}


    /**
     * @param string[] $objIds
     *
     * @return IliasTreeItem[]
     */
	private function fetchObjectData(array $objIds)
	{

		if (!count($objIds)) {
			return array();
		}
		$sql = "SELECT
                object_data.*,
                tree.child AS ref_id,
                tree.parent AS parent_ref_id,
                page_object.parent_id AS page_layout,
                cs.value AS timeline
                FROM object_data
                  INNER JOIN object_reference ON (object_reference.obj_id = object_data.obj_id AND object_reference.deleted IS NULL)
                  INNER JOIN tree ON (tree.child = object_reference.ref_Id)
                  LEFT JOIN page_object ON page_object.parent_id = object_data.obj_id
                  LEFT JOIN container_settings AS cs ON cs.id = object_data.obj_id AND cs.keyword = 'news_timeline'
                WHERE (object_data.obj_id IN (" . implode(',', $objIds) . ") AND object_data.type NOT IN ('rolf', 'itgr'))
                GROUP BY object_data.obj_id;";
		$set = $this->db->query($sql);
		$return = array();

		while ($row = $this->db->fetchAssoc($set)) {

			if ($this->isRead($row['ref_id'])) {
				$row['permissionType'] = "read";
			} elseif ($this->isVisible($row['ref_id'])) {
				$row['permissionType'] = "visible";
			} else {
				continue;
			}

			$treeItem = new IliasTreeItem(
                strval($row['obj_id']),
                strval($row['title']),
                strval($row['description']),
                ($row['page_layout'] !== NULL),
                (intval($row['timeline']) === 1),
                strval($row['permissionType']),
                strval($row['ref_id']),
                strval($row['parent_ref_id']),
                strval($row['type']),
                strval(\ilLink::_getStaticLink($row['ref_id'], $row['type'])),
                $this->createRepoPath($row['ref_id'])
            );

			$treeItem = $this->fixSessionTitle($treeItem);
            $return[] = $treeItem;
		}

		return $return;
	}

	private function fixSessionTitle(IliasTreeItem $treeItem) {
	    if($treeItem->getType() === "sess") {
            $appointment = ilSessionAppointment::_lookupAppointment($treeItem->getObjId());
            $title = strlen($treeItem->getTitle()) ? (': '. $treeItem->getTitle()) : '';
            $title = ilSessionAppointment::_appointmentToString($appointment['start'], $appointment['end'],$appointment['fullday']) . $title;
            return $treeItem->setTitle($title);
        }

        return $treeItem;
    }


	/**
	 * @param $ref_id int
	 * @return array
	 */
	protected function createRepoPath($ref_id)
	{
		global $tree;
		$path = array();
		foreach ($tree->getPathFull($ref_id) as $node) {
			$path[] = strval($node['title']);
		}

		return $path;
	}


	/**
	 * Checks the access right of the given $refId for visible permission.
	 *
	 * @param $refId int a ref_id to check the access
	 *
	 * @return bool true if the permission is visible, otherwise false
	 */
	private function isVisible($refId) {
		return $this->access->checkAccess('visible', '', $refId);
	}


	/**
	 * Checks the access right of the given $refId for read permission.
	 *
	 * @param $refId int a ref_id to check the access
	 *
	 * @return bool true if the permission is read, otherwise false
	 */
	private function isRead($refId) {
		return $this->access->checkAccess('read', '', $refId);
	}
}