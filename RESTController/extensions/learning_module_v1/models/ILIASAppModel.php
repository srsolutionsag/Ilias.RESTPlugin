<?php namespace RESTController\extensions\ILIASApp\V1;

use CallbackFilterIterator;
use ilAccessHandler;
use ilDBInterface;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Filesystem;
use ilObject;
use ilObjFileBasedLMAccess;
use ilUtil;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RESTController\extensions\ILIASApp\V2\data\HttpStatusCodeAnswer;
use RESTController\libs as Libs;
use SplFileInfo;
use ilException;

require_once('./Modules/File/classes/class.ilObjFile.php');


final class ILIASAppModel extends Libs\RESTModel {

    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var ilAccessHandler
     */
    private $access;
    /**
     * @var Filesystem
     */
    private $filesystem;


    public function __construct() {
        global $DIC;
        Libs\RESTilias::loadIlUser();
        $this->db = $DIC->database();
        $this->access = $DIC->access();
        $this->filesystem = $DIC->filesystem()->web();
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
        if (strlen($startFile) === 0) {
            // We are unable to find all the information for the entry point therefore no resource is found
            return ["body" => new HttpStatusCodeAnswer("No entry point found for HTLM object"), "status" => 404];
        }

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

            $startPath = ilObjFileBasedLMAccess::_determineStartUrl($objId);

            if (strlen($startPath) > 0) {
                $lmDir = './' . ilUtil::getWebspaceDir() . '/lm_data/lm_' . $objId . '/';
                return str_replace($lmDir, '', $startPath);
            }

            return "";
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

        try {
            // build the source and target paths
            $clientName = CLIENT_ID;
            $rootDir = "data/$clientName";
            $lmDirName = "lm_$objId";
            $timestamp = $this->getMaxTimeStampRecursively($rootDir, "lm_data/$lmDirName");
            $restLmDir = "rest/lm_zip_files";
            $targetZipFile = "$restLmDir/$lmDirName/$timestamp.zip";
            $rootTarget = "$rootDir/$targetZipFile";

            // make sure that the download directory for the learning module exists
            $this->filesystem->createDir("$restLmDir/$lmDirName");

            // if necessary, compress the learning module
            $success = true;
            if(!$this->filesystem->has($targetZipFile)) {
                // empty the directory
                $entries = $this->filesystem->listContents("$restLmDir/$lmDirName");
                foreach($entries as $e) {
                    $path = $e->getPath();
                    if($e->isFile()) $this->filesystem->delete($path);
                    else $this->filesystem->deleteDir($path);
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
     * @param $timestamp int
     * @return int the timestamp
     * @throws DirectoryNotFoundException
     */
    function getMaxTimeStampRecursively(&$root, $path, &$timestamp = -1) {
        $timestamp = max(stat("$root/$path")["mtime"], $timestamp);
        $entries = $this->filesystem->listContents($path);
        foreach($entries as $e) {
            $path = $e->getPath();
            $timestamp = $e->isFile() ? max(stat("$root/$path")["mtime"], $timestamp) : $this->getMaxTimeStampRecursively($root, $path, $timestamp);
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
        if(!(defined("PATH_TO_ZIP") && defined("PATH_TO_UNZIP"))) return false;
        $workingDir = getcwd();

        try {
            // navigate to directory of source
            chdir($sourceDir);
            $targetFilePath = ilUtil::escapeShellArg(str_repeat("../", count(explode("/", $sourceDir))) . $targetFilePath);
            $zipFileBlacklistOption = $this->buildInlineStringListForShell(
                $this->generateFileIgnoreList($source)
            );

            // zip and check result
            $zipCmd = "$zipFileBlacklistOption -r $targetFilePath $source";
            $strError = "error";
            $result = ilUtil::execQuoted(PATH_TO_ZIP, $zipCmd);
            $result = implode(" | ", $result);
            if(strpos($result, $strError) !== false && strpos($zipCmd, $strError) === false) return false;
        } finally {
            // navigate back to working dir
            chdir($workingDir);
        }

        return true;
    }

    private function buildInlineStringListForShell(array $args) {
        if (count($args) === 0) {
            return "";
        }
        $escapedArgs = [];
        foreach ($args as $arg) {
            $escapedArgs[] = ilUtil::escapeShellArg($arg);
        }

        $flatList = join(" ", $escapedArgs);

        return "-x $flatList";
    }

    private function generateFileIgnoreList($path) {
        $ignoreList = [];
        $basePath = realpath($path);
        $zipFileIterator = new CallbackFilterIterator(
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)),
            function (SplFileInfo $current) {
                return $current->isFile() && $current->getExtension() === "zip";
            }
        );
        /**
         * @var SplFileInfo $zip
         */
        foreach ($zipFileIterator as $zip) {
            $zipPath = $zip->getRealPath();
            if ($this->isZippedSahsModule($zipPath)) {
                // Relative to zip root
                $sourceRoot = str_replace("$basePath", $path, $zipPath);
                $ignoreList[] = $sourceRoot;
            }
        }

        return $ignoreList;
    }

    private function isZippedSahsModule($zipPath) {
        $escapedPath = ilUtil::escapeShellArg($zipPath);
        $zipCmd = "-Z1 $escapedPath";
        $execResult = ilUtil::execQuoted(PATH_TO_UNZIP, $zipCmd);
        $blacklistedFiles = array_filter($execResult, function ($entry) {
            return $entry === "imsmanifest.xml";
        });

        return count($blacklistedFiles) > 0;
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