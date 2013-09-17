<?php
namespace SAF\Framework;

/**
 * Import data into the application from array
 */
class Import_Array
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

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

	//---------------------------------------------------------------------------------------- import
	/**
	 * @param $array array two dimension (keys are row and column number) array
	 */
	public function importArray($array)
	{
		$row = reset($array);
		if (!isset($this->class_name)) {
			$this->class_name = Namespaces::fullClassName(reset($row));
			unset($array[key($array)]);
		}
		$builder = new Object_Builder_Array($this->class_name);
		echo "<pre>" . print_r($builder->buildCollection($this->class_name, $array, true), true) . "</pre>";
	}

	//------------------------------------------------------------------------------------- importRow
	/**
	 * @param $row string[] one dimension (key is column number) array
	 */
	public function importRow($row)
	{
		echo "import " . $this->class_name . " : " . print_r($row, true) . "<br>";
	}

}
