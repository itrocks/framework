<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Annotation\Class_;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Use a specific HTML builder class to build output / edit / object for write for the property
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Widget implements Has_Set_Final
{
	use Common;

	//------------------------------------------------------------------------------------------ AUTO
	const AUTO = 'auto';

	//------------------------------------------------------------------------------------------ SORT
	const SORT = 'sort';

	//----------------------------------------------------------------------------------- $class_name
	public string $class_name;

	//-------------------------------------------------------------------------------------- $options
	public array $options;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $class_name = self::AUTO, string ...$options)
	{
		$this->class_name = $class_name;
		$this->options    = $options;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->class_name;
	}

	//------------------------------------------------------------------------------------- hasOption
	/**
	 * @param $option string  @values self::SORT
	 * @return boolean
	 */
	public function hasOption(string $option) : bool
	{
		return in_array($option, $this->options, true);
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Property $reflection) : void
	{
		if ($this->class_name !== self::AUTO) {
			return;
		}
		$type = $reflection->getType();
		if (!$type->isClass() || $type->isAbstractClass()) {
			return;
		}
		$widget_annotation = Class_\Widget::of($type->asReflectionClass());
		if (!$widget_annotation->value) {
			return;
		}
		$this->class_name = $widget_annotation->value;
		$this->options    = $widget_annotation->options;
	}

}
