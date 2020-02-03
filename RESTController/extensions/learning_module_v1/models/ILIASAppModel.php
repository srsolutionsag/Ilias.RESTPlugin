<?php namespace RESTController\extensions\ILIASApp\V1;

use ILIAS\Filesystem\Exception\IOException;
use ilObjFileBasedLM;
use ilObjFileBasedLMAccess;
use ilUtil;
use RESTController\extensions\ILIASApp\V2\data\ErrorAnswer;
use RESTController\libs as Libs;

require_once('./Modules/File/classes/class.ilObjFile.php');


final class ILIASAppModel extends Libs\RESTModel {

    /**
     * @var \ilDB
     */
    private $db;

    /**
     * @var \ilAccessHandler
     */
    private $access;


    public function __construct() {
        global $ilDB, $ilAccess;
        Libs\RESTilias::loadIlUser();
        $this->db = $ilDB;
        $this->access = $ilAccess;
    }

    /**
     * collects the directories, files and startfile for a learning module
     *
     * @param $refId string reference of the learning module
     * @return array body and status
     * @throws IOException
     */
    public function getLearningModuleData($refId) {
        // check access
        if(!($this->isVisible($refId) && $this->isRead($refId)))
            return ["body" => new ErrorAnswer("Forbidden"), "status" => 403];

        // get objectId of the learning module
        $file = new ilObjFileBasedLM($refId);
        $objId = $file->getId();

        // get learning module data
        $startFile = $this->getStartFile($objId);
        $zipResult = $this->getZipFile($objId);
        
        return ["body" => array(
            "startFile" => $startFile,
            "zipFile" => $zipResult["file"],
            "zipDirName" => $zipResult["dirName"]
        )];
	}

    /**
     * reads the start file of the FileBasedLM with the given object-id
     *
     * @param $objId integer objectId of the learning module
     * @return string
     */
	private function getStartFile($objId) {
        $startFile = ilObjFileBasedLMAccess::_determineStartUrl($objId);
        // assume format [PATH_TO_LEARNING_MODULES]/lm_[OBJID]/[PATH_TO_LM_START_FILE]
        $ind = 8 + strpos($startFile, "lm_$objId/");
        return substr($startFile, $ind);
    }

    /**
     * makes sure that the requested lm is available in compressed form for the download via the app and returns a path for the resource
     *
     * @param $objId integer objectId of the learning module
     * @return array<string> the absolute path to the compressed learning module
     * @throws IOException
     */
    private function getZipFile($objId) {
        global $DIC;
        // build the source and target paths
        $clientName = CLIENT_ID;
        $rootDir = "data/$clientName";
        $restLmDir = "rest/lm_zip_files";
        $lmDirName = "lm_$objId";
        $target = "$rootDir/$restLmDir/$lmDirName.zip";
        // make sure that the download directory for learning modules of the REST plugin exists
        $fsWeb = $DIC->filesystem()->web();
        $fsWeb->createDir($restLmDir);
        // compress the learning module
        $this->zip("$rootDir/lm_data", $lmDirName, $target); // TODO dev react when zipping failed
        return ["file" => $target, "dirName" => $lmDirName];
    }

    /**
     * creates a zip of the provided source (file or directory) and places it at the provided target
     *
     * @param $sourceDir string path to source that will be zipped
     * @param $source string file or directory name that will be zipped
     * @param $targetFilePath string file that will contain the zipped source
     * @return bool true if the zip-command was executed successfully and false otherwise
     */
    private function zip($sourceDir, $source, $targetFilePath) {
        if(!PATH_TO_ZIP) return false;
        $workingDir = getcwd();
        // navigate to directory of source
        chdir($sourceDir);
        $targetFilePath = str_repeat("../", count(explode("/", $sourceDir))) . $targetFilePath;
        // zip and check result
        $zipCmd = "-r " . ilUtil::escapeShellArg($targetFilePath) . " " . $source;
        $strError = "error";
        $result = ilUtil::execQuoted(PATH_TO_ZIP, $zipCmd);
        $result = implode(" | ", $result);
        if(strpos($result, $strError) !== false && strpos($zipCmd, $strError) === false) return false;
        // navigate back to working dir
        chdir($workingDir);
        return true;
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
}