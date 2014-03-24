<?php
namespace SAF\AOP;

use ReflectionProperty;

/**
 * Object representation of a Php property read from source
 */
class Php_Property
{

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Php_Class
	 */
	public $class;

	//-------------------------------------------------------------------------------------- $default
	/**
	 * @var string
	 */
	public $default;

	//-------------------------------------------------------------------------------- $documentation
	/**
	 * @var string
	 */
	public $documentation;

	//--------------------------------------------------------------------------------------- $indent
	/**
	 * @var string
	 */
	public $indent;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @var string
	 */
	public $name;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @var Php_Property
	 */
	private $parent;

	//--------------------------------------------------------------------------------------- $static
	/**
	 * @var string 'static' or null
	 */
	public $static;

	//----------------------------------------------------------------------------------- $visibility
	/**
	 * @var string 'private', 'protected', 'public' or null (implicitly public)
	 */
	public $visibility;

	//------------------------------------------------------------------------------------- fromMatch
	/**
	 * @param $class Php_Class
	 * @param $match array
	 * @param $n     integer
	 * @return Php_Property
	 */
	public static function fromMatch(Php_Class $class, $match, $n = null)
	{
		$property = new Php_Property();
		$property->class = $class;
		if (isset($n)) {
			$property->indent            = $match[1][$n];
			$property->documentation     = empty($match[2][$n]) ? null : $match[2][$n];
			$property->visibility        = ($match[3][$n] == 'var') ? 'public' : $match[3][$n];
			$property->static            = empty($match[4][$n]) ? null : $match[4][$n];
			$property->name              = $match[5][$n];
			$property->default           = empty($match[6][$n]) ? null : $match[6][$n];
		}
		else {
			$property->indent            = $match[1];
			$property->documentation     = empty($match[2]) ? null : $match[2];
			$property->visibility        = ($match[3] == 'var') ? 'public' : $match[3];
			$property->static            = empty($match[4]) ? null : $match[4];
			$property->name              = $match[5];
			$property->default           = empty($match[6]) ? null : $match[6];
		}
		return $property;
	}

	//-------------------------------------------------------------------------------- fromReflection
	/**
	 * @param $class      Php_Class
	 * @param $reflection ReflectionProperty
	 * @return Php_Property
	 */
	public static function fromReflection(Php_Class $class, ReflectionProperty $reflection)
	{
		$property = new Php_Property();
		$property->class = $class;
		$defaults = $reflection->getDeclaringClass()->getDefaultProperties();
		$property->default = isset($defaults[$reflection->name]) ? $defaults[$reflection->name] : null;
		$property->documentation = $reflection->getDocComment();
		$property->name = $reflection->name;
		return $property;
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @return Php_Method
	 */
	public function getParent()
	{
		if (!isset($this->parent)) {
			$this->parent = false;
			$class_parent = $this->class->getParent();
			if ($class_parent) {
				$properties = $class_parent->getProperties();
				if (!isset($properties[$this->name])) {
					$properties = $class_parent->getProperties(['traits']);
					if (!isset($properties[$this->name])) {
						$properties = $class_parent->getProperties(['inherited']);
					}
				}
				if (isset($properties[$this->name])) {
					$this->parent = $properties[$this->name];
				}
			}
		}
		return $this->parent ?: null;
	}

	//----------------------------------------------------------------------------------------- regex
	/**
	 * @param $property_name string
	 * @return string
	 */
	public static function regex($property_name = null)
	{
		$name = isset($property_name) ? $property_name : '\w+';
		return '%'
		. '(\n\s*?)'                                // 1 : indent
		. '(?:(/\*\*\n(?:\s*\*.*\n)*\s*\*/)\n\s*)?' // 2 : documentation
		. '(?:\/\*.*\*/\n\s*)?'                     // ignored one-line documentation
		. '(private|protected|public|var)\s+'       // 3 : visibility
		. '(?:(static)\s+)?'                        // 4 : static
		. '\$(' . $name . ')\s*'                    // 5 : name
		// . '(?:\=\s*((?:.*?\n?)*?)\s*)?'             // 6 : default : crashes with Macros;;$macros
		// . ';\s*\n'
		. '%';
	}

}
