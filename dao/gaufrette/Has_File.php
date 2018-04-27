<?php
namespace ITRocks\Framework\Dao\Gaufrette;

use Exception;

/**
 * File_Link could be used by classes requiring a single file content
 */
trait Has_File
{

	//--------------------------------------------------------------------------------- $file_content
	/**
	 * @dao        gaufrette
	 * @max_length 1000000000
	 * @store      gz
	 * @var string
	 */
	public $file_content;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * File name use for download.
	 * Not used for storage. For storage, framework will generate unique file name
	 *
	 * @null
	 * @var string
	 */
	public $file_name;

	//--------------------------------------------------------------------------------- $storage_name
	/**
	 * File name used to force storage name.
	 * BEWARE : must be unique per object! Avoid to use it and prefer to let it null
	 * This value is not stored in the Dao, so must be computed!
	 * If used, should use with a @override storage_name @getter method ensuring uniqueness
	 *
	 * @computed
	 * @null
	 * @read_only
	 * @setter
	 * @store false
	 * @user  invisible readonly
	 * @var string
	 */
	public $storage_name;

	//-------------------------------------------------------------------------------- setStorageName
	/**
	 * Disable setter on storage name. Should only be computed
	 *
	 * @throws Exception
	 */
	protected function setStorageName()
	{
		throw new Exception(
			'File_Link::$storage_name should only be computed with @getter and return unique value'
		);
	}

}
