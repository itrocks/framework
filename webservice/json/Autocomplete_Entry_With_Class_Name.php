<?php
namespace ITRocks\Framework\Webservice\Json;

/**
 * Auto-complete entry with class name
 */
class Autocomplete_Entry_With_Class_Name extends Autocomplete_Entry
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $id         integer
	 * @param $value      string
	 * @param $class_name string
	 */
	public function __construct($id = null, $value = null, $class_name = null)
	{
		parent::__construct($id, $value);
		if ($class_name) {
			$this->class_name = $class_name;
		}
	}

}
