<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

/**
 * a target selector can be used to define where the result is loaded (#messages as default)
 *
 * @example @annotation [[\Vendor\Module\]Class_Name::]featureName] [target_selector]
 */
class Method_Target_Annotation extends Method_Annotation
{

	//--------------------------------------------------------------------------------------- $target
	/**
	 * @var string
	 */
	public $target = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $class_property  Reflection
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $class_property, $annotation_name)
	{
		if (strpos($value, SP)) {
			list($value, $this->target) = explode(SP, $value, 2);
		}
		parent::__construct($value, $class_property, $annotation_name);
	}

	//------------------------------------------------------------------------------------ asHtmlData
	/**
	 * @return string
	 */
	public function asHtmlData()
	{
		list($class_name, $method_name) = explode('::', $this->value);
		$class_name = Builder::current()->sourceClassName($class_name);
		return str_replace(BS, SL, $class_name) . SL . $method_name
			. ($this->target ? (SP . $this->target) : '');
	}

}
