<?php namespace RESTController\extensions\ILIASApp\V3;

use ilContainerReference;
use ilLPStatus;
use ilObject;
use ilSessionAppointment;
use RESTController\extensions\ILIASApp\V2\data\ErrorAnswer;
use RESTController\extensions\ILIASApp\V2\data\HttpStatusCodeAnswer;
use RESTController\extensions\ILIASApp\V2\data\IliasTreeItem;
use RESTController\libs as Libs;

require_once('./Modules/File/classes/class.ilObjFile.php');


final class ILIASAppModel extends Libs\RESTModel {

    /**
     * @var \ilDBInterface
     */
    private $db;

    /**
     * @var \ilAccessHandler
     */
    private $access;
    /**
     * Holds all reference types which may use the
     * title of the element they are referring to.
     *
     * @var string[]
     */
    private static $REFERENCE_TYPES = [
        'grpr',
        'catr',
        'crsr'
    ];


    public function __construct() {
        global $DIC;
        Libs\RESTilias::loadIlUser();
        $this->db = $DIC->database();
        $this->access = $DIC->access();

        /* Some objects like the learning sequence load the template reference
         * in the constructor which fails because there is no template.
		 *
		 * Therefore create a stub template entry which stops these object from crashing.
		 */
        if (!$DIC->offsetExists('tpl')) {
            $DIC['tpl'] = new \stdClass();
        }
    }

    public function getObjectByRefId($refId)
    {
        try {
            // check access
            if (!($this->isVisible($refId) && $this->isRead($refId))) {
                return ["body" => new HttpStatusCodeAnswer("Forbidden"), "status" => 403];
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
                WHERE (object_reference.ref_id = $refId AND object_data.type NOT IN ('rolf', 'itgr')) 
                LIMIT 1;";

            $set = $this->db->query($sql);
            $row = $this->db->fetchAssoc($set);

            if ($this->isRead($row['ref_id'])) {
                $row['permissionType'] = "read";
            } else {
                $row['permissionType'] = "visible";
            }

            $treeItem = new IliasTreeItem(
                strval($row['obj_id']),
                strval($row['title']),
                strval($row['description']),
                ($row['page_layout'] !== null),
                (intval($row['timeline']) === 1),
                strval($row['permissionType']),
                strval($row['ref_id']),
                strval($row['parent_ref_id']),
                strval($row['type']),
                strval(\ilLink::_getStaticLink($row['ref_id'], $row['type'])),
                $this->createRepoPath($row['ref_id'])
            );

            $treeItem = $this->fixSessionTitle($treeItem);
            $treeItem = $this->fixReferenceTitle($treeItem);

            return ["body" => $treeItem, "status" => 200];
        } catch (\Exception $exception) {
            return ["body" => new ErrorAnswer("Internal Server Error"), "status" => 500];
        }
    }


    /**
     * collects the metadata of a file with reference $refId
     *
     * @param $refId int
     * @param $userId int
     * @return array
     */
    public function getFileData($refId, $userId) {
        // check access
        if(!$this->isVisible($refId))
            return ["body" => new ErrorAnswer("Forbidden"), "status" => 403];

        // get file data
        $file = new \ilObjFile($refId);
        // file name
        $fileName = mb_strtolower($file->getFileName());
        $fileName = preg_replace('/[^a-z0-9\-_\.]+/', '', $fileName);

        // learning progress
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $q = "SELECT status FROM ut_lp_marks WHERE obj_id=" . $file->getId() . " AND usr_id=" . $userId;
        $ret = $ilDB->query($q);
        $fileLearningProgress = boolval($ilDB->fetchAssoc($ret)["status"]);

        return ["body" => array(
            'fileExtension' => $file->getFileExtension(),
            'fileName' => $fileName,
            'fileSize' => strval($file->getFileSize()),
            'fileType' => $file->getFileType(),
            'fileVersion' => strval($file->getVersion()),
            'fileVersionDate' => $file->getLastUpdateDate(),
            'fileLearningProgress' => $fileLearningProgress
        )];
    }

    /**
     * sets the lp-status for a file with reference $refId and user $userId to completed
     *
     * @param $refId int
     * @param $userId int
     * @return array
     */
    public function setFileLearningProgressToDone($refId, $userId) {
        // check access
        if(!($this->isVisible($refId) && $this->isRead($refId)))
            return ["body" => new ErrorAnswer("Forbidden"), "status" => 403];

		// set state
		$file = new \ilObjFile($refId);
		$status = ilLPStatus::LP_STATUS_COMPLETED_NUM;

		global $DIC;
		$ilDB = $DIC['ilDB'];
		$q = "INSERT INTO ut_lp_marks (obj_id, usr_id, status) VALUES({$file->getId()}, {$userId}, {$status})
		      ON DUPLICATE KEY UPDATE status={$status}";
		$result = $ilDB->query($q);

		if($result === false)
			return ["body" => new ErrorAnswer("Bad Request"), "status" => 400];

		return ["body" => array("message" => "Learning progress was successfully set to done")];
	}

    /**
     * collects the parameters for the theming of the app
     *
     * @param $timestamp integer timestamp of the last synchronization in the app
     * @return array
     */
    public function getThemeData($timestamp) {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $q = "SELECT * FROM ui_uihk_pegasus_theme WHERE id=1";
        $ret = $ilDB->query($q);
        if($ret === false)
            return ["body" => new ErrorAnswer("Internal Server Error"), "status" => 500];
        $dat = $ilDB->fetchAssoc($ret);

        $clientName = CLIENT_ID;
        $iconsDir = "data/$clientName/pegasushelper/theme/icons/";
        $keys = ["course", "file", "folder", "group", "learningplace", "learningmodule", "link"];
        $resources = [];
        if($timestamp < intval($dat["timestamp"]))
            foreach($keys as $key)
                $resources[] = ["key" => $key, "path" => $iconsDir . "icon_$key.svg"];

        return ["body" => array(
            "themePrimaryColor" => $dat["primary_color"],
            "themeContrastColor" => boolval($dat["contrast_color"]),
            "themeTimestamp" => intval($dat["timestamp"]),
            "themeIconResources" => $resources
        )];
    }

    /**
     * Checks the access right of the given $refId for visible permission.
     *
     * @param $refId int a ref_id to check the access
     * @return bool true if the permission is visible, otherwise false
     */
    private function isVisible($refId) {
        return $this->access->checkAccess('visible', '', $refId);
    }


    /**
     * Checks the access right of the given $refId for read permission.
     *
     * @param $refId int a ref_id to check the access
     * @return bool true if the permission is read, otherwise false
     */
    private function isRead($refId) {
        return $this->access->checkAccess('read', '', $refId);
    }

    /**
     * Fixes the title for reference repository objects.
     *
     * @param IliasTreeItem $treeItem   The item which may need a title fix.
     *
     * @return IliasTreeItem            A clone of the ilias tree item with the fixed title.
     */
    private function fixReferenceTitle(IliasTreeItem $treeItem) {
        if(in_array($treeItem->getType(), self::$REFERENCE_TYPES)) {
            require_once './Services/ContainerReference/classes/class.ilContainerReference.php';
            $targetTitle = ilContainerReference::_lookupTitle($treeItem->getObjId());
            $treeItem = $treeItem->setTitle($targetTitle);
        }
        return $treeItem;
    }

    private function fixSessionTitle(IliasTreeItem $treeItem) {
        if($treeItem->getType() === "sess") {
            // required for ILIAS 5.2
            require_once './Modules/Session/classes/class.ilSessionAppointment.php';

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
    private function createRepoPath($ref_id)
    {
        global $DIC;
        $path = array();
        foreach ($DIC->repositoryTree()->getPathFull($ref_id) as $node) {
            $path[] = strval($node['title']);
        }

        return $path;
    }
}