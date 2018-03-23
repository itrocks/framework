<?php
namespace ITRocks\Framework\Configuration\File\Source;

use ITRocks\Framework\Configuration\File;
use ITRocks\Framework\Configuration\File\Source;

/**
 * Builder class source file writer
 *
 * @override file @var Source
 * @property Source file
 */
class Writer extends File\Writer
{

	//---------------------------------------------------------------------------- writeConfiguration
	/**
	 * Write builder configuration to lines
	 */
	protected function writeConfiguration()
	{
		$this->lines[] = '';
	}

}
