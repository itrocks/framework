<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Feature\Import\Import_Export_Format;
use ITRocks\Framework\Feature\Import\Import_Worksheet;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Class;
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
	 * @mandatory
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
	 * @param $class_name string|null
	 */
	public function __construct(string $class_name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return Names::classToDisplay($this->class_name) . ' :'
			. SP . $this->format
			. SP . Loc::tr('import');
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class
	 */
	public function getClass() : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection valid */
		return new Reflection_Class($this->class_name);
	}

}
