<?php
namespace SAF\Framework;

abstract class Instantiator
{

	//-------------------------------------------------------------------------------- $substitutions
	/**
	 * Class names substitutions table : indexes are the parent class names, values the herited class names
	 *
	 * @var multitype:string
	 */
	private static $substitutions = array();

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * Get substituted class name for a given class name
	 * 
	 * If there is no substitution for class name, it will be returned unchanged.
	 * 
	 * @param string $class_name
	 * @return string
	 */
	public static function getClass($class_name)
	{
		return isset(self::$substitutions[$class_name])
			? self::$substitutions[$class_name]
			: $class_name;
	}

	//----------------------------------------------------------------------------------- newInstance
	/**
	 * Returns a new instance of given class name, using substition table if exists. 
	 *
	 * @param string $class_name
	 * @return object
	 */
	public static function newInstance($class_name)
	{
		$class_name = static::getClass($class_name);
		return new $class_name();
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register a substitution
	 *
	 * If a substitution has already been registered for $class_name, this will register the lower level class between $herited_class and the already registered class.
	 * $herited_class must be a subclass of $class_name, or an error will occur.
	 *
	 * @param string $class_name    the parent class
	 * @param string $herited_class the herited class that will always replace the parent class
	 */
	public static function register($class_name, $herited_class)
	{
		if (is_subclass_of($herited_class, $class_name)) {
			if (!($already = self::$substitutions[$class_name])) {
				self::$substitutions[$class_name] = $herited_class;
			}
			else {
				if (is_subclass_of($herited_class, $already)) {
					self::$substitutions[$class_name] = $herited_class;
				}
			}
		}
		else {
			trigger_error("Can't substitute : $herited_class is not subclass of $class_name");
		}
	}

}
