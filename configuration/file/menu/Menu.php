<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Widget\Menu\Block;

/**
 * The menu.php configuration file
 */
class Menu extends File
{

	//--------------------------------------------------------------------------------------- $blocks
	/**
	 * @var Block[]
	 */
	public $blocks;

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read()
	{
		(new Menu\Reader($this))->read();
	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * Write to file
	 */
	public function write()
	{
		(new Menu\Writer($this))->write();
	}

}
