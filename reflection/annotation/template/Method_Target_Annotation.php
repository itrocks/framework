<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Interfaces\Reflection;

/**
 * a target selector can be used to define where the result is loaded (#responses as default)
 *
 * @example @annotation [[\Vendor\Module\]Class_Name::]featureName] [target_selector]
 */
class Method_Target_Annotation extends Method_Annotation
{

	//--------------------------------------------------------------------------------------- $target
	/**
	 * @var ?string
	 */
	public ?string $target = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           ?string
	 * @param $class_property  Reflection
	 * @param $annotation_name string
	 */
	public function __construct(?string $value, Reflection $class_property, string $annotation_name)
	{
		if (str_contains($value, SP)) {
			[$value, $this->target] = explode(SP, $value, 2);
		}
		parent::__construct($value, $class_property, $annotation_name);
	}

	//------------------------------------------------------------------------------------ asHtmlData
	/**
	 * @param $object object|null The reference object, if set
	 * @return string
	 */
	public function asHtmlData(object $object = null) : string
	{
		if (str_contains($this->value, SL) && !str_contains($this->value, '::')) {
			return $this->value;
		}
		$identifier                 = $object ? Dao::getObjectIdentifier($object) : null;
		[$class_name, $method_name] = explode('::', $this->value);
		$class_name                 = Builder::current()->sourceClassName($class_name);
		return str_replace(BS, SL, $class_name)
			. ($identifier ? (SL . $identifier) : '')
			. SL . $method_name
			. ($this->target ? (SP . $this->target) : '');
	}

}
