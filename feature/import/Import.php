<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Feature\Import\Import_Export_Format;
use ITRocks\Framework\Feature\Import\Import_Worksheet;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;

/**
 * Standard import class for your objects data
 */
#[Store]
class Import
{

	//----------------------------------------------------------------------------------- $class_name
	public string $class_name;

	//----------------------------------------------------------------------------------------- $file
	public ?File $file;

	//--------------------------------------------------------------------------------------- $format
	public ?Import_Export_Format $format;

	//----------------------------------------------------------------------------------- $worksheets
	/** @var Import_Worksheet[] */
	public array $worksheets = [];

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $class_name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return Names::classToDisplay($this->class_name) . ' :'
			. SP . $this->format
			. SP . Loc::tr('import');
	}

	//-------------------------------------------------------------------------------------- getClass
	public function getClass() : Reflection_Class
	{
		/** @noinspection PhpUnhandledExceptionInspection valid */
		return new Reflection_Class($this->class_name);
	}

}
