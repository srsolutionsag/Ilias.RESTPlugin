<?php

namespace RESTController\extensions\eBook;

use \RESTController\libs as Libs;

class EBookModel  extends Libs\RESTModel {
	/**
	 * @var \ilDB
	 */
	protected $db;

	/**
	 * @var \ilAccessHandler
	 */
	protected $access;


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
	 * @param $user_id int
	 *
	 * @return mixed
	 */
	public function getEBooks($user_id) {
		global $DIC;

		$access = $DIC->access();
		$config = new \ileBookConfig($DIC->database());
		$root_ref = $config->get('library_root_ref_id');


		$query = "
				SELECT id, ref.ref_id, data.title, data.description, data.create_date, data.last_update FROM rep_robj_xebk_data
				INNER JOIN object_data data ON data.obj_id = rep_robj_xebk_data.id 
				INNER JOIN object_reference ref ON ref.obj_id = data.obj_id 
				WHERE is_online = 1 AND ref.deleted is NULL 
				";
		$set = $this->db->query($query);
		$books = [];
		while($res = $this->db->fetchAssoc($set)) {
			if(!$access->checkAccessOfUser($user_id, "read", "", $res['ref_id']))
				continue;
			$path = $this->createPath($res, $root_ref);
			$res['id'] = (int) $res['id'];
			$res['ref_id'] = (int) $res['ref_id'];
			$res['path'] = $path;
			// Path must contain root ref. otherwise we are not in scope.
			if(0 == count(array_filter($path, function ($element) use ($root_ref) {
				return $element['ref_id'] == $root_ref;
			})))
				continue;
			$books[] = $res;
		}
		return $books;
	}


	/**
	 * @param $res  array
	 * @param $root_ref
	 *
	 * @return \array[]
	 */
	private function createPath($res, $root_ref) {
		global $tree;
		$path = $tree->getNodePath($res['ref_id']);
		$path = array_map(function($element) {
			return ["ref_id"=> $element['child'], "title" =>$element['title']];
		}, $path);
		$path_ids = array_map(function($e) {
			return $e['ref_id'];
		}, $path);
		$slice_id = array_search($root_ref, $path_ids);
		if($slice_id === false)
			return [];
		return array_slice($path, $slice_id, -1);
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

		return $object->getEncryptedLegacyFilePath();
	}


	public function getKeyByRefId($user_id, $ref_id) {
		if(!$this->checkAccessOfUser($user_id, $ref_id))
			throw new NoAccessException();

		$object = new \ilObjeBook($ref_id);

		if (!$object->hasFile())
			throw new NoFileException("There's no key to this ref id.");

		return $object->getLegacySecret();
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


	/**
	 * @param $message string
	 * @param $stackTrace string
	 * @param $version string
	 * @param $os string
	 */
	public function errorLog($message, $stackTrace, $version, $os) {
		global $DIC;

		$db = $DIC->database();
		$query = "INSERT INTO rep_robj_xebk_errors (message, stack_trace, version, os, `timestamp`) VALUES (
				{$db->quote($message, 'text')},
				{$db->quote($stackTrace, 'text')},
				{$db->quote($version, 'text')},
				{$db->quote($os, 'text')},
				{$db->quote('NOW', 'date')}
			)";
		$db->manipulate($query);
	}

}