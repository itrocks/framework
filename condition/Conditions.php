<?php
namespace ITRocks\Framework\Condition;

use ITRocks\Framework\Condition;

/**
 * A condition can be a "multiple conditions"
 */
class Conditions
{

	//----------------------------------------------------------------------------------- $conditions
	/**
	 * @var Condition[]
	 */
	public $conditions = [];

	//----------------------------------------------------------------------------------------- $link
	/**
	 * @values Logical::const
	 * @var string
	 */
	public $link = Logical::_AND;

}
