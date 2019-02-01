<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Traits\Has_Name;

/**
 * Feature class
 *
 * @business
 * @override name @translate common
 */
class Feature_Class
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string the name of the source class
	 * @param $name       string the displayed name (matches @display of the built class)
	 */
	public function __construct($class_name = null, $name = null)
	{
		if (isset($class_name)) {
			$this->class_name = $class_name;
		}
		if (isset($name)) {
			$this->name = $name;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return Loc::tr($this->name);
	}

}
