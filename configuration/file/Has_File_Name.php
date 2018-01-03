<?php
namespace ITRocks\Framework\Configuration\File;

/**
 * Construct with $file_name
 */
trait Has_File_Name
{

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var string
	 */
	public $file_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor : needs the name of the file
	 *
	 * @param $file_name string
	 */
	public function __construct($file_name = null)
	{
		if (isset($file_name)) {
			$this->file_name = $file_name;
		}
	}

}
