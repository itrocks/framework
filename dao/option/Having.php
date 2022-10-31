<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * Allow to add conditions to a query, outside of a Group_By context
 */
class Having implements Option
{
	use Has_In;

	//----------------------------------------------------------------------------------- $conditions
	/**
	 * @var array
	 */
	public array $conditions;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $conditions array
	 */
	public function __construct(array $conditions = [])
	{
		$this->conditions = $conditions;
	}

}
