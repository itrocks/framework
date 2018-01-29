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
	 * - if the object already exists into data store, then an exception will occurred
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
				}
			}
			// else nothing to do
		}
		// update
		else {
			if ($existing_object) {
				if (Dao::getObjectIdentifier($existing_object) != Dao::getObjectIdentifier($this->auto_link_object )) {
					$this->replaceCurrentByExisting($existing_object);
				}
			} else {
				$this->link->disconnect($this->auto_link_object);
			}
		}
	}

}
