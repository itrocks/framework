<?php
namespace ITRocks\Framework\PHP\Dependency;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Option\Group_By;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ReflectionException;

/**
 * Dependency tool functions
 *
 * @extends Dependency
 */
trait Tools
{

	//----------------------------------------------------------------------------- $dependency_class
	/**
	 * @var string[]
	 */
	static $dependency_class;

	//--------------------------------------------------------------- classesWithPropertiesUsingClass
	/**
	 * Get dependencies to class that declare which property(ies) has var $class_name
	 *
	 * @param $class_name string
	 * @return string[] class names that contain one or more matching properties
	 */
	public static function classesWithPropertiesUsingClass(string $class_name) : array
	{
		$used_by = Dao::search(
			[
				'declaration'     => Dependency::T_PROPERTY_DECLARATION,
				'dependency_name' => $class_name,
				'type'            => Dependency::T_VAR
			],
			Dependency::class,
			new Group_By('class_name')
		);
		$class_names = [];

		foreach ($used_by as $dependency) {
			$class_names[$dependency->class_name] = $dependency->class_name;
		}
		return $class_names;
	}

	//----------------------------------------------------------------------------- dependencyToClass
	/**
	 * @param $dependency_name ?string
	 * @return ?string
	 */
	public static function dependencyToClass(?string $dependency_name) : ?string
	{
		if (!$dependency_name) {
			return null;
		}
		if (!static::$dependency_class) {
			if (file_exists(Cache::CACHE_DIR . '/dependency_class.php')) {
				/** @noinspection PhpIncludeInspection file_exists */
				static::$dependency_class = include(Cache::CACHE_DIR . '/dependency_class.php');
			}
			else {
				/** @var $dependency Dependency */
				$dependency = Dao::searchOne(
					['dependency_name' => Func::equal($dependency_name), 'type' => Dependency::T_SET],
					Dependency::class
				);
				return $dependency ? $dependency->class_name : null;
			}
		}
		return static::$dependency_class[$dependency_name] ?? null;
	}

	//------------------------------------------------------------------------------------ extendsUse
	/**
	 * Get all classes that extend / use this class name
	 *
	 * @param $class_name    string
	 * @param $include_class boolean if true, $class_name is included if not an abstract class
	 * @param $extend_types  string|string[] values from Dependency's T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return string[]
	 */
	public static function extendsUse(
		string $class_name,
		bool $include_class = false,
		$extend_types = [Dependency::T_EXTENDS, Dependency::T_USE]
	) : array
	{
		$children = Dao::search(
			[
				'dependency_name' => $class_name,
				'type'            => $extend_types
			],
			Dependency::class
		);
		$class_names = [];
		if ($include_class) {
			try {
				if (!(new Reflection_Class($class_name))->isAbstract()) {
					$class_names[$class_name] = $class_name;
				}
			}
			catch (ReflectionException) {
			}
		}
		foreach ($children as $child) {
			foreach (static::extendsUse($child->class_name, true) as $child_class_name) {
				$class_names[$child_class_name] = $child_class_name;
			}
		}
		return $class_names;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Get a dependency which index and type match
	 * Use caching to avoid several identical queries to be executed
	 *
	 * @param $what string[] need keys type with allowed value, and allowed index
	 * @return ?Dependency
	 * @see Dependency\Cache::INDEXES
	 * @see Dependency\Cache::TYPES
	 */
	public static function getType(array $what) : ?Dependency
	{
		$search = [];
		$type   = $what['type'];
		// get dependency (can be null) from cache
		foreach (Dependency\Cache::INDEXES as $index) {
			if (isset($what[$index])) {
				$key = $what[$index];
				if (isset(Dependency\Cache::$$index[$key])) {
					$dependencies = Dependency\Cache::$$index[$key];
					return $dependencies[$type] ?? null;
				}
				$search[$index] = Dao\Func::equal($key);
			}
		}
		$search['type'] = Dependency\Cache::TYPES;
		/** @var $dependencies Dependency[] */
		$dependencies = Dao::search($search, Dependency::class, Dao::key('type'));
		// store found dependencies into all caches
		if ($dependencies) {
			$dependency = reset($dependencies);
			foreach (Dependency\Cache::INDEXES as $index) {
				Dependency\Cache::$$index[$dependency->$index] = $dependencies;
			}
			return $dependencies[$type] ?? null;
		}
		// store null result into matching cache
		foreach (Dependency\Cache::INDEXES as $index) {
			if (isset($what[$index])) {
				Dependency\Cache::$$index[$what[$index]] = [];
			}
		}
		return null;
	}

	//---------------------------------------------------------------------------------------- hasSet
	/**
	 * @param $class_name ?string
	 * @return boolean
	 */
	public static function hasSet(?string $class_name) : bool
	{
		if (!$class_name) {
			return false;
		}
		if (!static::$dependency_class) {
			if (file_exists(Cache::CACHE_DIR . '/dependency_class.php')) {
				/** @noinspection PhpIncludeInspection file_exists */
				static::$dependency_class = include(Cache::CACHE_DIR . '/dependency_class.php');
			}
			else {
				return boolval(Dao::searchOne(
					['class_name' => Func::equal($class_name), 'type' => Dependency::T_SET],
					Dependency::class
				));
			}
		}
		return in_array($class_name, static::$dependency_class);
	}

	//-------------------------------------------------------------------------- propertiesUsingClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_names string|string[]
	 * @return Reflection_Property[]
	 */
	public static function propertiesUsingClass($class_names) : array
	{
		$properties = [];
		foreach ((is_array($class_names) ? $class_names : [$class_names]) as $class_name) {
			foreach (static::classesWithPropertiesUsingClass($class_name) as $dependency_class_name) {
				/** @noinspection PhpUnhandledExceptionInspection comes from valid dependency */
				$class            = new Reflection_Class($dependency_class_name);
				$class_properties = $class->getProperties([T_EXTENDS, T_USE]);
				foreach ($class_properties as $property) {
					$property_class_name = $property->getType()->getElementTypeAsString();
					if (is_a($property_class_name, $class_name, true)) {
						// which class inherit / use this property ?
						$use_class_names = static::extendsUse($class->name, true);
						foreach ($use_class_names as $use_class_name) {
							$key_use_class_name = Builder::current()->sourceClassName($use_class_name);
							/** @noinspection PhpUnhandledExceptionInspection comes from valid dependency */
							$properties["$key_use_class_name($property->name)"] = new Reflection_Property(
								$use_class_name, $property->name
							);
						}
					}
				}
			}
		}
		return $properties;
	}

}
