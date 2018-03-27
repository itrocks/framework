<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Source\Class_Use;
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
