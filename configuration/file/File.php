<?php
namespace ITRocks\Framework\Configuration;

use ITRocks\Framework\Configuration\File\Has_File_Name;
use ITRocks\Framework\Configuration\File\Read;
use ITRocks\Framework\Configuration\File\Write;

/**
 * Configuration file
 */
class File
{
	use Has_File_Name;

	//--------------------------------------------------------------------------------------- $config
	/**
	 * Configuration file content
	 *
	 * @var string[]
	 */
	protected $config;

	//------------------------------------------------------------------------------- $included_files
	/**
	 * Included configuration sub-files, if there are some (eg builder.php, menu.php, etc.)
	 *
	 * @var File[]
	 */
	protected $included_files = [];

	//------------------------------------------------------------------------------------------- add
	/**
	 * Adds sub-elements to a sub-section path
	 *
	 * @param $path string[]
	 * @param $add  array
	 */
	public function add(array $path, array $add)
	{

	}

	//------------------------------------------------------------------------------------------ read
	/**
	 * @return static
	 */
	public function read()
	{
		$this->config = (new Read($this->file_name))->read();
		return $this;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove sub-elements from a sub-section path
	 *
	 * @param $path   string[]
	 * @param $remove array
	 */
	public function remove(array $path, array $remove)
	{

	}

	//----------------------------------------------------------------------------------------- write
	/**
	 * @return static
	 */
	public function write()
	{
		(new Write($this->file_name))->write();
		foreach ($this->included_files as $included_file) {
			$included_file->write();
		}
		$this->included_files = [];
		return $this;
	}

}
