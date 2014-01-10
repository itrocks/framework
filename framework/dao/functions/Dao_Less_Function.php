<?php
namespace SAF\Framework;

/**
 * Lesser than is a condition used to get the record where the column has a value lesser than the
 * given value
 */
class Dao_Less_Function extends Dao_Comparison_Function
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $than_value mixed
	 */
	public function __construct($than_value = null)
	{
		parent::__construct("<", $than_value);
	}

}
