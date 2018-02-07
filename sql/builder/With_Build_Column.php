<?php
namespace ITRocks\Framework\Sql\Builder;

use ITRocks\Framework\Sql\Join;
use ITRocks\Framework\Sql\Join\Joins;

/**
 * The interface for sql builder classes that use Has_Build_Column (used for parameter control)
 */
interface With_Build_Column
{

	//----------------------------------------------------------------------------------- buildColumn
	/**
	 * @param $path            string  The path of the property
	 * @param $join            Join    For optimisation purpose, if join is already known
	 * @param $as              boolean If false, prevent 'AS' clause to be added
	 * @param $resolve_objects boolean If true, a property path for an object will be replace with a
	 *                         CONCAT of its representative values
	 * @return string
	 */
	public function buildColumn($path, $as = true, $resolve_objects = false, Join $join = null);

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Joins
	 */
	public function getJoins();

}
