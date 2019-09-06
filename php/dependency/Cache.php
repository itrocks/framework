<?php
namespace ITRocks\Framework\PHP\Dependency;

use ITRocks\Framework\PHP\Dependency;

/**
 * Dependencies cache manager
 *
 * Read only the first time, then store into caches and read from cache
 */
class Cache
{

	//--------------------------------------------------------------------------------------- INDEXES
	const INDEXES = ['class_name', 'dependency_name'];

	//----------------------------------------------------------------------------------------- TYPES
	const TYPES = [Dependency::T_SET, Dependency::T_STORE];

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var Dependency[] key is the class name of the dependency
	 */
	public static $class_name = [];

	//------------------------------------------------------------------------------ $dependency_name
	/**
	 * @var Dependency[] key is the name of the dependency
	 */
	public static $dependency_name = [];

}
