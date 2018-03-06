<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Config\Priority;

/**
 * The menu.php configuration file
 */
class Config extends File
{

	//-------------------------------------------------------------------------- $plugins_by_priority
	/**
	 * @var Priority[]
	 */
	public $plugins_by_priority;

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read()
	{
		(new Config\Reader($this))->read();
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write to file
	 */
	public function write()
	{
		(new Config\Writer($this))->write();
	}

}
