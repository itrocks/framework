<?php
namespace SAF\Framework;

use SAF\Framework\Dao\File;
use SAF\Framework\Import\Import_Export_Format;
use SAF\Framework\Import\Import_Worksheet;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Tools\Names;

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
	 * @var Import_Export_Format
	 */
	public $format;

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @link Object
	 * @var File
	 */
	public $file;

	//----------------------------------------------------------------------------------- $worksheets
	/**
	 * @var Import_Worksheet[]
	 */
	public $worksheets = [];

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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return Names::classToDisplay($this->class_name) . ' :'
			. SP . $this->format
			. SP . Loc::tr('import');
	}

}
