<?php
namespace ITRocks\Framework\Configuration;

use ITRocks\Framework\Application;
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

	//------------------------------------------------------------------------------- defaultFileName
	/**
	 * Calculates the name of the default configuration file matching the current configuration class
	 *
	 * The $short_file_name is useless most of times : calculated using the name of the current class
	 *
	 * @param $short_file_name string ie 'builder', 'config', 'menu'
	 * @return string
	 */
	public static function defaultFileName($short_file_name = null)
	{
		if (!$short_file_name) {
			$short_file_name = strtolower(rLastParse(static::class, BS));
		}
		return str_replace(BS, SL, strtolower(Application::current()->getNamespace()))
			. SL . $short_file_name . '.php';
	}

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
