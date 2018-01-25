<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Dao\Mysql\Link;
use mysqli_result;

/**
 * Auto_Link_Manager : Allow to manage the creation and update of autoLink trait
 */
class Immutable_Manager
{

	//----------------------------------------------------------------------------- $auto_link_object
	/**
	 * @var Object
	 */
	private $auto_link_object;

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @var Data_Link
	 */
	private $link;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Called before write, this ensures that the object will be immutable into the data link
	 * - if the object already exists into data store, then an exception will occured
	 * - if the object does not already exists, then save the object as a new one
	 *
	 * @param $link Data_Link   Data_Link
	 * @param $auto_link_object Object
	 */
	public function __construct(Data_Link $link, $auto_link_object)
	{
		$this->auto_link_object = $auto_link_object;
		if ($link) {
			$this->link = $link;
		}
		else {
			$this->link = Dao::current();
		}

		// Remove spaces
		foreach (get_object_vars($this->auto_link_object) as $key => $value) {
			if (is_string($value)) {
				$this->auto_link_object->$key = str_replace('  ', ' ', trim($value));
			}
		}
	}

	// FIXME Will be moved to an asynchronous cron
//	//---------------------------------------------------------------------- deleteOldObjectIfNotUsed
//	/**
//	 * Delete previous object from database if not used in any linked tables
//	 *
//	 * @param $previous_object Object Object to delete if not used
//	 */
//	private function deleteOldObjectIfNotUsed($previous_object)
//	{
//		$id_to_delete = DAO::getObjectIdentifier($previous_object);
//		if ($id_to_delete) {
//			$class             = get_class($previous_object);
//			$explode           = explode(BS, $class);
//			$id_name_to_search = 'id_' . strtolower(end($explode));
//			$dao               = Dao::current();
//			if ($dao instanceof Link && $id_name_to_search) {
//				$tables = $dao->query(
//					"SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE column_name = "
//					. Q . $id_name_to_search . Q,
//					AS_ARRAY
//				);
//				if (!$tables || count($tables) == 0) {
//					$dao->delete($previous_object);
//				}
//				else {
//					$exists = false;
//					foreach ($tables as $table) {
//						$found = $dao->query(
//							'SELECT count(*) FROM ' . BQ . $table['TABLE_NAME'] . BQ
//							. SP . 'WHERE' . SP
//							. BQ . $table['TABLE_NAME'] . BQ . DOT . BQ . $id_name_to_search . BQ
//							. SP . '=' . SP . DAO::getObjectIdentifier($this->auto_link_object)
//						);
//						/** @var $found mysqli_result */
//						if ($found) {
//							$row = $found->fetch_row();
//							if ($row && $row[0]) {
//								$exists = true;
//								break;
//							}
//						}
//					}
//					if (!$exists) {
//						$dao->delete($previous_object);
//					}
//				}
//			}
//		}
//	}

	//---------------------------------------------------------------------- replaceCurrentByExisting
	/**
	 * Replace $auto_link_object by an existing value
	 *
	 * @param $existing_object Object
	 */
	private function replaceCurrentByExisting($existing_object)
	{
		$this->link->disconnect($this->auto_link_object);
		$this->link->replace($this->auto_link_object, $existing_object, false);
	}

	//------------------------------------------------------------------------------------------- run
	public function run()
	{
		// Creation or update ?
		// no id --> creation
		$creation = !DAO::getObjectIdentifier($this->auto_link_object);

		$working_object = clone $this->auto_link_object;
		$this->link->disconnect($working_object);
		$existing_object = $this->link->searchOne($working_object);

		if ($creation) {
			// Object exists then use it ?
			if ($existing_object) {
				if (Dao::getObjectIdentifier($existing_object) != Dao::getObjectIdentifier($this->auto_link_object )) {
					$this->replaceCurrentByExisting($existing_object);
					// FIXME Will be moved to an asynchronous cron
//					if ($existing_object) {
//						$this->deleteOldObjectIfNotUsed($existing_object);
//					}
				}
			}
		}
		// update
		else {
			if ($existing_object) {
				if (Dao::getObjectIdentifier($existing_object) != Dao::getObjectIdentifier($this->auto_link_object )) {
					$this->replaceCurrentByExisting($existing_object);
				}
			}
		}
	}

}
