<?php
namespace ITRocks\Framework\Dao\File\Session_File;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Dao\File\Session_File;
use Serializable;

/**
 * Temporary files collection, stored into session
 *
 * Files contents are emptied on serialize, so please always set the temporary file name
 * TODO could serialize / unserialize into File instead of here, with write of temporary file if does not exist
 */
class Files implements Serializable
{

	//---------------------------------------------------------------------------------------- $files
	/**
	 * @link Collection
	 * @var File[]
	 */
	public $files;

	//--------------------------------------------------------------------------------- addAndGetLink
	/**
	 * Adds a file and gets a link to this file
	 *
	 * @param $file File
	 * @return string
	 */
	public function addAndGetLink(File $file)
	{
		$name_hash               = $file->nameHash();
		$this->files[$name_hash] = $file;
		return str_replace(BS, SL, Session_File::class) . SL . Feature::F_OUTPUT . SL . $name_hash;
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string
	 */
	public function serialize()
	{
		$serialized = [];
		foreach ($this->files as $file) {
			$serialized[$file->nameHash()] = [$file->name, $file->temporary_file_name];
		}
		return serialize($serialized);
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $serialized string
	 * @return void
	 */
	public function unserialize($serialized)
	{
		$this->files = [];
		foreach (unserialize($serialized) as $file_hash => $serialized_file) {
			/** @noinspection PhpUnhandledExceptionInspection constant */
			$file                      = Builder::create(File::class);
			$this->files[$file_hash]   = $file;
			$file->name                = reset($serialized_file);
			$file->temporary_file_name = end($serialized_file);
		}
	}

}
