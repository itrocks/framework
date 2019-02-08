<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Feature\Import\Import_Export_Format;
use ITRocks\Framework\Feature\Import\Import_Worksheet;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Names;

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

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @link Object
	 * @var File
	 */
	public $file;

	//--------------------------------------------------------------------------------------- $format
	/**
	 * @link Object
	 * @var Import_Export_Format
	 */
	public $format;

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
