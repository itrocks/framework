<?php
namespace ITRocks\Framework\Dao;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link\Identifier_Map;

/**
 * This is a set of records linked to Dao tools
 */
class Set
{

	//------------------------------------------------------------------------------------ $data_link
	/**
	 * @var Identifier_Map
	 */
	public $data_link;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $data_link Identifier_Map
	 */
	public function __construct(Identifier_Map $data_link = null)
	{
		$this->data_link = $data_link ?: Dao::current();
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * Replaces all objects in data link that match search with the given objects collection
	 * If search is null and $class_name is an object : $class_name will be the search
	 *
	 * @param $objects    object[] objects may not have any object identifier into data link
	 * @param $class_name string|object
	 * @param $search     string[]|object
	 */
	public function replace(array $objects, $class_name, $search = null)
	{
		$dao = $this->data_link;
		// change $class_name to string, change $search to search object or array
		if (is_object($class_name)) {
			if (!isset($search)) {
				$search = $class_name;
			}
			$class_name = get_class($class_name);
		}
		// write new objects
		if ($dao instanceof Sql\Link) {
			$dao->begin();
		}
		$old_objects = $dao->search($search, $class_name);
		$old_identifiers = [];
		foreach ($old_objects as $old_object) {
			$old_key = $this->searchKey($old_object);
			$old_identifiers[$old_key] = $dao->getObjectIdentifier($old_object);
		}
		$written = [];
		foreach ($objects as $object) {
			$key = $this->searchKey($object);
			if (isset($old_identifiers[$key])) {
				$written[$old_identifiers[$key]] = true;
			}
			else {
				$written[$dao->getObjectIdentifier($dao->write($object))] = true;
			}
		}
		if ($dao instanceof Sql\Link) {
			$dao->commit();
			$dao->begin();
		}
		// delete old unused objects
		foreach ($old_objects as $key => $object) {
			if (!isset($written[$key])) {
				$dao->delete($object);
			}
		}
		if ($dao instanceof Sql\Link) {
			$dao->commit();
		}
	}

	//------------------------------------------------------------------------------------- searchKey
	/**
	 * Gets the object as a search key (full string)
	 *
	 * @param $object object The object which stores the values
	 * @return string The resulting search key
	 */
	private function searchKey($object)
	{
		$keys = [];
		foreach (array_keys(get_class_vars(get_class($object))) as $property_name) {
			$keys[$property_name] = isset($object->$property_name) ? $object->$property_name : '';
		}
		ksort($keys);
		return join(':', $keys);
	}

}
