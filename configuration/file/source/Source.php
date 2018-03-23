<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Source\Class_Use;

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

	//------------------------------------------------------------------------------------ $class_use
	/**
	 * @var Class_Use[]
	 */
	public $class_use;

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
