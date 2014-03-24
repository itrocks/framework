<?php
namespace SAF\Framework;

use SAF\Plugins;
use SAF\Plugins\Register;

/**
 * Mysql delete-and-replace feature
 *
 * When a mysql error #1451 'a foreign key constraint fails' occurs on a 'DELETE' query, a
 * little HTML form explains the error and proposes to replace the record by another one,
 * selected into a combo-box.
 */
class Mysql_Delete_And_Replace implements Plugins\Registerable
{

	//------------------------------------------------------------------------------------- extractId
	/**
	 * Extract the object identifier from a short standard DELETE query string
	 *
	 * @param $query string
	 * @return integer
	 */
	private function extractId($query)
	{
		$id = rLastParse($query, ' WHERE id = ');
		return is_numeric($id) ? intval($id) : null;
	}

	//--------------------------------------------------------------------------------------- onError
	/**
	 * @param $query  string
	 * @param $object Contextual_Mysqli
	 */
	public function onError($query, Contextual_Mysqli $object)
	{
		if (
			in_array(
				$object->last_errno,
				[Mysql_Errors::ER_ROW_IS_REFERENCED, Mysql_Errors::ER_ROW_IS_REFERENCED_2]
			)
			&& $object->context
			&& is_string($object->context)
			&& $object->isDelete($query)
		) {
			$id = $this->extractId($query);
			if ($id) {
				$controller_uri = SL . $object->context . SL . $id . SL . 'deleteAndReplace';
				echo (new Main_Controller())->runController($controller_uri, ['as_widget' => true]);
			}
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod([Contextual_Mysqli::class, 'query'], [$this, 'onError']);
	}

}
