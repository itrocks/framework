<?php
namespace ITRocks\Framework\Property;

use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ReflectionException;

/**
 * Property path flexible parsing tools
 */
class Path
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//-------------------------------------------------------------------------------- $property_path
	/**
	 * @var string
	 */
	public $property_path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    string
	 * @param $property_path string property path, reverse joins allowed
	 */
	public function __construct(string $class_name, string $property_path = '')
	{
		$this->class_name    = $class_name;
		$this->property_path = $property_path;
	}

	//---------------------------------------------------------------------------------------- exists
	/**
	 * @return boolean
	 */
	public function exists()
	{
		$property_class_name = $this->toPropertyClassName();
		return Reflection_Property::exists(reset($property_class_name), end($property_class_name));
	}

	//------------------------------------------------------------------------------- toPropertyClass
	/**
	 * @return Reflection_Class|Reflection_Property
	 * @throws ReflectionException
	 */
	public function toPropertyClass()
	{
		$property_class_name = $this->toPropertyClassName();
		return (count($property_class_name) > 1)
			? new Reflection_Property(reset($property_class_name), end($property_class_name))
			: new Reflection_Class(reset($property_class_name));
	}

	//--------------------------------------------------------------------------- toPropertyClassName
	/**
	 * @return string[] [$class_name[, $property_path]]
	 */
	public function toPropertyClassName()
	{
		$class_name    = $this->class_name;
		$property_path = $this->property_path;

		if ($open_position = strrpos($property_path, '(')) {
			$class_position = strrpos(substr($property_path, 0, $open_position), DOT) ?: 0;
			if ($class_position) {
				$class_position ++;
			}
			$class_name    = substr($property_path, $class_position, $open_position - $class_position);
			$class_name    = Names::pathToClass($class_name);
			$property_path = ($property_position = strpos($property_path, DOT, $open_position))
				? substr($property_path, $property_position + 1)
				: null;
		}

		return $property_path ? [$class_name, $property_path] : [$class_name];
	}

}
