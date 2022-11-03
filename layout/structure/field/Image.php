<?php
namespace ITRocks\Framework\Layout\Structure\Field;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Layout\Structure\Element;

/**
 * Image field : here to display image
 */
class Image extends Element
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @var File
	 */
	public File $file;

	//------------------------------------------------------------------------------------------ dump
	/**
	 * @param $level integer
	 * @return string
	 */
	public function dump(int $level = 0) : string
	{
		return parent::dump($level) . ' = ' . $this->file->name;
	}

}
