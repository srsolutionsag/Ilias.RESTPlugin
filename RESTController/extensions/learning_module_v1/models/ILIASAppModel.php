<?php namespace RESTController\extensions\ILIASApp\V1;

use ilAccessHandler;
use ilDB;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Filesystem;
use ilObject;
use ilObjFileBasedLMAccess;
use ilUtil;
use RESTController\extensions\ILIASApp\V2\data\HttpStatusCodeAnswer;
use RESTController\libs as Libs;

require_once('./Modules/File/classes/class.ilObjFile.php');


final class ILIASAppModel extends Libs\RESTModel {

    /**
     * @var ilDB
     */
    private $db;

    /**
     * @var ilAccessHandler
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
     */
    public function getLearningModuleData($refId) {
        // check access
        if(!($this->isVisible($refId) && $this->isRead($refId)))
            return ["body" => new HttpStatusCodeAnswer("Forbidden"), "status" => 403];

        // get objectId and type of the object
        $objId = ilObject::_lookupObjId($refId);
        $type = ilObject::_lookupType($objId);

        // check if the object is a supported lm
        if($type !== "htlm" && $type !== "sahs")
            return ["body" => new HttpStatusCodeAnswer("Forbidden"), "status" => 403];

        // get learning module data
        $startFile = $this->getStartFile($objId, $type);
        $zipResult = $this->getCompressedLearningModule($objId);

        // if the compression failed, return status 500
        if(!$zipResult["success"])
            return ["body" => new HttpStatusCodeAnswer("Internal Server Error"), "status" => 500];
        
        return ["body" => array(
            "startFile" => $startFile,
            "zipFile" => $zipResult["file"],
            "zipDirName" => $zipResult["dirName"],
            "timestamp" => $zipResult["timestamp"],
        )];
	}

    /**
     * reads the start file of the FileBasedLM with the given object-id
     *
     * @param $objId integer objectId of the lm
     * @param $type string type of the lm
     * @return string
     */
	private function getStartFile($objId, $type) {
        if($type === "htlm") {
            $startFile = ilObjFileBasedLMAccess::_determineStartUrl($objId);
            // assume format [PATH_TO_LEARNING_MODULES]/lm_[OBJID]/[PATH_TO_LM_START_FILE]
            $ind = 8 + strpos($startFile, "lm_$objId/");
            return substr($startFile, $ind);
        } else { // type is sahs
            // assume that imsmanifest.xml is placed in root folder
            return "imsmanifest.xml";
        }
    }

    /**
     * makes sure that the requested lm is available in compressed form for the download via the app and returns a path for the resource
     *
     * @param $objId integer objectId of the learning module
     * @return array<mixed> the result of the compression
     */
    private function getCompressedLearningModule($objId) {
        global $DIC;
        $fsWeb = $DIC->filesystem()->web();

        try {
            // build the source and target paths
            $clientName = CLIENT_ID;
            $rootDir = "data/$clientName";
            $lmDirName = "lm_$objId";
            $timestamp = $this->getMaxTimeStampRecursively($rootDir, "lm_data/$lmDirName", $fsWeb);
            $restLmDir = "rest/lm_zip_files";
            $targetZipFile = "$restLmDir/$lmDirName/$timestamp.zip";
            $rootTarget = "$rootDir/$targetZipFile";

            // make sure that the download directory for the learning module exists
            $fsWeb->createDir("$restLmDir/$lmDirName");

            // if necessary, compress the learning module
            $success = true;
            if(!$fsWeb->has($targetZipFile)) {
                // empty the directory
                $entries = $fsWeb->listContents("$restLmDir/$lmDirName");
                foreach($entries as $e) {
                    $path = $e->getPath();
                    if($e->isFile()) $fsWeb->delete($path);
                    else $fsWeb->deleteDir($path);
                }
                // compress the learning module
                $success = $this->zip("$rootDir/lm_data", $lmDirName, $rootTarget);
            }

            return ["file" => $rootTarget, "dirName" => $lmDirName, "success" => $success, "timestamp" => $timestamp];
        } catch (IOException $e) {
            return ["success" => false];
        }
    }

    /**
     * checks the last modified timestamps of all files and subdirectories of the directory or
     * file at $root/$path and returns the latest value
     *
     * @param $root string path from the working directory of the running php script to the working directory of the $fileSystem
     * @param $path string relative path to the target directory or file
     * @param $fileSystem Filesystem
     * @param $timestamp int
     * @return int the timestamp
     * @throws DirectoryNotFoundException
     */
    function getMaxTimeStampRecursively(&$root, $path, &$fileSystem, &$timestamp = -1) {
        $timestamp = max(stat("$root/$path")["mtime"], $timestamp);
        $entries = $fileSystem->listContents($path);
        foreach($entries as $e) {
            $path = $e->getPath();
            $timestamp = $e->isFile() ? max(stat("$root/$path")["mtime"], $timestamp) : $this->getMaxTimeStampRecursively($root, $path, $fileSystem, $timestamp);
        }
        return $timestamp;
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