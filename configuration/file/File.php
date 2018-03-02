<?php
namespace ITRocks\Framework\Configuration;

use ITRocks\Framework\Configuration\File\Has_File_Name;
use ITRocks\Framework\Configuration\File\Reader;
use ITRocks\Framework\Configuration\File\Writer;

/**
 * Configuration file
 */
abstract class File
{
	use Has_File_Name;

	//---------------------------------------------------------------------------------- $begin_lines
	/**
	 * free code lines before the parsed configuration started
	 *
	 * @var string[]
	 */
	public $begin_lines;

	//------------------------------------------------------------------------------------ $end_lines
	/**
	 * free code lines after the parsed configuration ended
	 *
	 * @var string[]
	 */
	public $end_lines;

	//------------------------------------------------------------------------------------ $namespace
	/**
	 * @var string
	 */
	public $namespace;

	//------------------------------------------------------------------------------------------ $use
	/**
	 * @var string[]
	 */
	public $use;

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read()
	{
		(new Reader($this))->read();
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write to file
	 */
	public function write()
	{
		(new Writer($this))->write();
	}

}
