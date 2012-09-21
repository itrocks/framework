<?php
namespace SAF\Framework;

class Foreignlink_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $value
	 * @param Reflection_Property $reflection_object
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		parent::__construct($value);
		if (!$this->value) {
			$this->value = Names::classToProperty($reflection_property->getAnnotation("var"));
			if (substr($this->value, 0, 10) === "multitype:") {
				$this->value = substr($this->value, 10);
			}
		}
	}

}
