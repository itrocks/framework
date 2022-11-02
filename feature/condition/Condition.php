<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Tools\Date_Time;

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
	public string $class_name;

	//------------------------------------------------------------------------------------------ $now
	/**
	 * This property can be used for the special 'now' property, a condition that depends on the
	 * system, not the data
	 *
	 * @link DateTime
	 * @store false
	 * @var Date_Time
	 */
	public Date_Time $now;

	//---------------------------------------------------------------------------------------- $title
	/**
	 * A title for the condition : depends on the place the condition editor system is used
	 *
	 * @store false
	 * @var string
	 */
	public string $title = 'Condition editor';

	//---------------------------------------------------------------------------------------- $where
	/**
	 * A condition is the sum of Where logical / calculation functions, and nothing else
	 *
	 * The root condition is always a Func\Logical and / or structure,
	 * that may contain Func\Logical sub-structures, with no recursion limit,
	 * and as final Func\Comparison or Func\In
	 *
	 * Nothing else is allowed at this time
	 *
	 * @store json
	 * @var Logical
	 */
	public Logical $where;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string|null
	 * @param $where      Logical|null
	 */
	public function __construct(string $class_name = null, Logical $where = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
		if (isset($where)) {
			$this->where = $where;
		}
	}

}
