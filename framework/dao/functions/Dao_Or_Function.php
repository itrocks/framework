<?php
namespace SAF\Framework;

/**
 * Dao OR operator for WHERE clause
 */
class Dao_Or_Function extends Dao_Logical_Function
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $arguments Dao_Where_Function[]|mixed[] key can be a property path or numeric if depends
	 * on main property part
	 */
	public function __construct($arguments = null)
	{
		parent::__construct(Dao_Logical_Function::OR_OPERATOR, $arguments);
	}

}
