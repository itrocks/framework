<?php
namespace ITRocks\Framework\PHP\Dependency;

use ITRocks\Framework\Dao;
use ITRocks\Framework\PHP\Dependency;

/**
 * Dependencies cache manager
 *
 * Read only the first time, then store into caches and read from cache
 */
class Cache
{

	//------------------------------------------------------------------------------------- CACHE_DIR
	const CACHE_DIR = 'cache/dependencies';
	
	//--------------------------------------------------------------------------------------- INDEXES
	const INDEXES = ['class_name', 'dependency_name'];

	//----------------------------------------------------------------------------------------- TYPES
	const TYPES = [Dependency::T_SET, Dependency::T_STORE];

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var Dependency[] key is the class name of the dependency
	 */
	public static array $class_name = [];

	//------------------------------------------------------------------------------ $dependency_name
	/**
	 * @var Dependency[] key is the name of the dependency
	 */
	public static array $dependency_name = [];

	//-------------------------------------------------------------------------------------- generate
	/**
	 * Generate cache files
	 */
	public function generate()
	{
		if (!is_dir(static::CACHE_DIR)) {
			mkdir(static::CACHE_DIR);
		}
		$buffer = "<?php return [\n";
		foreach (Dao::search(['type' => Dependency::T_SET], Dependency::class) as $dependency) {
			/** @var $dependency Dependency */
			$buffer .= "\t'$dependency->dependency_name' => '$dependency->class_name',\n";
		}
		$buffer .= "];\n";
		file_put_contents(static::CACHE_DIR . '/dependency_class.php', $buffer);
	}

}
