<?php
namespace SAF\Framework;

/**
 * Standard import class for your objects data
 */
class Import
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//--------------------------------------------------------------------------------------- $format
	/**
	 * @link Object
	 * @var \SAF\Framework\Import\Import_Export_Format
	 */
	public $format;

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @link Object
	 * @var \SAF\Framework\Dao\File
	 */
	public $file;

	//----------------------------------------------------------------------------------- $worksheets
	/**
	 * @var \SAF\Framework\Import\Import_Worksheet[]
	 */
	public $worksheets;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 */
	public function __construct($class_name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
	}

}
