<?php
namespace ITRocks\Framework\Widget;

use ITRocks\Framework\Dao\Func\Where;

/**
 * A condition
 *
 * @example
 * What can be a condition, in term of feature ?
 * - TRUE
 * - property.path.boolean
 * - property.path = value
 * - property.path.1 = property.path.2
 * - average(property.path.1, property.path.2, value)
 * - etc.
 */
class Condition
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * Base class name : all property paths will come from objects of this class
	 *
	 * @var string
	 */
	public $class_name;

	//---------------------------------------------------------------------------------------- $where
	/**
	 * A condition is the sum of Where logical / calculation functions, and nothing else
	 *
	 * @store json
	 * @var Where
	 */
	public $where;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $where      Where
	 */
	public function __construct($class_name = null, Where $where = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
		if (isset($where)) {
			$this->where = $where;
		}
	}

}
