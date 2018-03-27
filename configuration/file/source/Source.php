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

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write to file
	 */
	public function write()
	{
		(new Source\Writer($this))->write();
	}

}
