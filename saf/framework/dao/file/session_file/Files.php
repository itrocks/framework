<?php
namespace SAF\Framework\Dao\File\Session_File;

use SAF\Framework\Builder;
use SAF\Framework\Dao\File;
use Serializable;

/**
 * Temporary files collection, stored into session
 *
 * Files contents are emptied on serialize, so please always set the temporary file name
 * TODO could serialize / unserialize into File instead of here, with write of temporary file if
 * does not exist
 */
class Files implements Serializable
{

	//---------------------------------------------------------------------------------------- $files
	/**
	 * @link Collection
	 * @var File[]
	 */
	public $files;

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string
	 */
	public function serialize()
	{
		$serialized = [];
		foreach ($this->files as $file) {
			$serialized[$file->name] = $file->temporary_file_name;
		}
		return serialize($serialized);
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string
	 * @return void
	 */
	public function unserialize($serialized)
	{
		$this->files = [];
		foreach (unserialize($serialized) as $file_name => $temporary_file_name) {
			/** @var $file File */
			$file = Builder::create(File::class);
			$file->name = $file_name;
			$file->temporary_file_name = $temporary_file_name;
			$this->files[] = $file;
		}
	}

}
