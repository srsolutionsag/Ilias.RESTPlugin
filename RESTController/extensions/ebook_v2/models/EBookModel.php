<?php

namespace RESTController\extensions\eBook\v2\models;

require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/eBook/classes/class.ilObjeBook.php");
require_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/eBook/classes/class.ileBookConfig.php");
require_once(__DIR__ . "/NoAccessException.php");
require_once(__DIR__ . "/NoFileException.php");

use \RESTController\libs as Libs;

final class EBookModel  extends Libs\RESTModel {
	/**
	 * @var \ilDB
	 */
	private $db;

	/**
	 * @var \ilAccessHandler
	 */
	private $access;


	public function __construct($load_user = true)
	{
		global $ilDB, $ilAccess;
		if($load_user) {
			Libs\RESTilias::loadIlUser();
			Libs\RESTilias::initAccessHandling();
		}
		$this->db = $ilDB;
		$this->access = $ilAccess;
	}

	/**
	 * @param $user_id
	 * @param $ref_id
	 *
	 * @return string
	 * @throws NoAccessException
	 * @throws NoFileException
	 */
	public function getFilePathByRefId($user_id, $ref_id) {
		if(!$this->checkAccessOfUser($user_id, $ref_id))
			throw new NoAccessException();

		$object = new \ilObjeBook($ref_id);

		if (!$object->hasFile())
			throw new NoFileException();

		return $object->getEncryptedFilePath();
	}


	public function getKeyByRefId($user_id, $ref_id) {
		if(!$this->checkAccessOfUser($user_id, $ref_id))
			throw new NoAccessException();

		$object = new \ilObjeBook($ref_id);

		if (!$object->hasFile())
			throw new NoFileException("There's no key to this ref id.");

		return bin2hex($object->getSecret());
	}

	public function getIVByRefId($user_id, $ref_id) {
		if(!$this->checkAccessOfUser($user_id, $ref_id))
			throw new NoAccessException();

		$object = new \ilObjeBook($ref_id);

		if (!$object->hasFile())
			throw new NoFileException("There's no key to this ref id.");

		return bin2hex($object->getInitialVector());
	}

	private function checkAccessOfUser($user_id, $ref_id) {
		global $DIC;

		$access = $DIC->access();
		$db = $DIC->database();
		if(!$access->checkAccessOfUser($user_id, "read", "", $ref_id))
			return false;

		$query =   "SELECT * FROM rep_robj_xebk_data as data
					INNER JOIN object_reference ref ON ref.obj_id = data.id AND ref.ref_id = {$db->quote($ref_id, "integer")}
					WHERE data.is_online = 1";
		$set = $db->query($query);
		if($db->numRows($set) == 0)
			return false;

		return true;

	}

}