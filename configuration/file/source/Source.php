<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Application;
use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Source\Class_Use;
use ITRocks\Framework\Controller\Getter;
use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Configuration into a source code
 *
 * This is for class building into source code instead of into Builder
 */
class Source extends File
{

	//-------------------------------------------------------------------------------- $class_extends
	/**
	 * @var string
	 */
	public $class_extends;

	//----------------------------------------------------------------------------- $class_implements
	/**
	 * @var string[]
	 */
	public $class_implements;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//----------------------------------------------------------------------------------- $class_type
	/**
	 * @values class, interface, trait
	 * @var string
	 */
	public $class_type;

	//------------------------------------------------------------------------------------ $class_use
	/**
	 * @var Class_Use[]|string[]
	 */
	public $class_use;

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds class (extends), interface(s) (implements) or trait(s) (use) to the definition of the
	 * class
	 *
	 * @var $class_interfaces_traits string|string[] class, interface(s) and/or trait(s)
	 */
	public function add($class_interfaces_traits)
	{
		if (is_string($class_interfaces_traits)) {
			if (interface_exists($class_interfaces_traits) || trait_exists($class_interfaces_traits)) {
				$class_interfaces_traits = [$class_interfaces_traits];
			}
		}
		if (is_string($class_interfaces_traits)) {
			$this->class_extends = $class_interfaces_traits;
		}
		else {
			foreach ($class_interfaces_traits as $interface_trait) {
				if (interface_exists($interface_trait)) {
					arrayInsertSorted($this->class_implements, $interface_trait);
				}
				elseif (trait_exists($interface_trait)) {
					arrayInsertSorted($this->class_use, $interface_trait);
				}
				else {
					trigger_error('Interface or trait ' . $interface_trait . ' does not exist', E_USER_ERROR);
				}
			}
		}
	}

	//---------------------------------------------------------------------------------------- create
	/**
	 * Create a new final class in the application that extends $class_extends
	 *
	 * @param $class_extends string
	 * @return static::class
	 */
	public static function create($class_extends)
	{
		$namespace = Application::current()->getNamespace();
		if (beginsWith($class_extends, $namespace . BS)) {
			trigger_error(
				'You cannot create a final class from an existing final class ' . $class_extends,
				E_USER_ERROR
			);
		}
		$class_name = $namespace . BS . Getter::classNameWithoutVendorProject($class_extends);
		$source = new Source(
			strtolower(str_replace(BS, SL, lLastParse($class_name, BS)))
			. SL . rLastParse($class_name, BS)
		);
		$source->class_name = $class_name;
		$source->namespace  = lLastParse($class_name, BS);
		$source->addUseFor($class_extends);
		return $source;
	}

	//------------------------------------------------------------------------------- defaultFileName
	/**
	 * @param $class_name string Mandatory (default value for compatibility with parent only)
	 * @return string
	 */
	public static function defaultFileName($class_name = null)
	{
		return (new Reflection_Class($class_name))->getFileName();
	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read()
	{
		(new Source\Reader($this))->read();
	}

	//------------------------------------------------------------------------------ shortClassNameOf
	/**
	 * Simplify the name of the class using its longest reference into use,
	 * or its start from the current namespace
	 *
	 * @param $class_name        string
	 * @param $maximum_use_depth integer do not care about use greater than this backslashes counter
	 * @return string
	 */
	public function shortClassNameOf($class_name, $maximum_use_depth = 999)
	{
		$final_class_name = parent::shortClassNameOf($class_name, $maximum_use_depth);
		if (strpos($final_class_name, BS) && (lLastParse($class_name, BS) === $this->namespace)) {
			$final_class_name = rLastParse($class_name, BS);
		}
		return $final_class_name;
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write to file
	 */
	public function write()
	{
		(new Source\Writer($this))->write();
	}

}
