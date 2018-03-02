<?php
namespace ITRocks\Framework\Configuration\File;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Builder\Built;

/**
 * The builder.php configuration file
 */
class Builder extends File
{

	//-------------------------------------------------------------------------------------- $classes
	/**
	 * @var Built[]|string[] Built classes, or comments if trim begins with '/', or empty lines ''
	 */
	public $classes;

	//------------------------------------------------------------------------------------------ read
	/**
	 * Read from file
	 */
	public function read()
	{
		(new Builder\Reader($this))->read();
	}

}
