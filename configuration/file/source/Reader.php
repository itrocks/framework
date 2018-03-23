<?php
namespace ITRocks\Framework\Configuration\File\Source;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Source;

/**
 * Builder class source file reader
 *
 * @override file @var Source
 * @property Source file
 */
class Reader extends File\Reader
{

	//----------------------------------------------------------------------------------- isStartLine
	/**
	 * @param $line string
	 * @return boolean
	 */
	public function isStartLine($line)
	{
		return false;
	}

	//----------------------------------------------------------------------------- readConfiguration
	/**
	 * Read configuration : the main part of the file
	 */
	protected function readConfiguration()
	{
	}

}
