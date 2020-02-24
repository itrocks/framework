<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Class_\Display_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
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
	 * @noinspection PhpDocMissingThrowsInspection
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
		if ($this->class_name && !$this->name) {
			/** @noinspection PhpUnhandledExceptionInspection class name must be valid */
			$this->name = Display_Annotation::of(new Reflection_Class($this->class_name))->value;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name ? Loc::tr($this->name) : '';
	}

}
