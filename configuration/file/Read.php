<?php
namespace ITRocks\Framework\Configuration\File;

/**
 * Configuration file read
 */
class Read
{
	use Has_File_Name;

	//------------------------------------------------------------------------------------------ read
	/**
	 * @return string[]
	 */
	public function read()
	{
		return file($this->file_name);
	}

}
