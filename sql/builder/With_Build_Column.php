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
	 * @param $as              boolean If false, prevent 'AS' clause to be added
	 * @param $resolve_objects boolean If true, a property path for an object will be replace with a
	 *                         CONCAT of its representative values
	 * @param $join            Join|null For optimisation purpose, if join is already known
	 * @return string
	 */
	public function buildColumn(
		string $path, bool $as = true, bool $resolve_objects = false, Join $join = null
	) : string;

	//-------------------------------------------------------------------------------------- getJoins
	/**
	 * @return Joins
	 */
	public function getJoins() : Joins;

}
