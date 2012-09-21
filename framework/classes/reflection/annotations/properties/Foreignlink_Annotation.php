<?php
namespace SAF\Framework;

class Foreignlink_Annotation extends Annotation implements Reflection_Object_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $value
	 * @param Reflection_Property $reflection_object
	 */
	public function __construct($value, $reflection_object)
	{
		parent::__construct($value);
		if (!$this->value) {
			$this->value = Names::classToProperty($reflection_object->getAnnotation("var"));
			if (substr($this->value, 0, 10) === "multitype:") {
				$this->value = substr($this->value, 10);
			}
		}
	}

}
