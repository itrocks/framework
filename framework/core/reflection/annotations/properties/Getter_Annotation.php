<?php
namespace SAF\Framework;

class Getter_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		parent::__construct($value);
		if (empty($this->value)) {
			$link = ($reflection_property->getAnnotation("link")->value);
			if (!empty($link)) {
				$this->value = "Aop::get" . $link;
			}
		}
	}

}
