<?php
namespace SAF\Framework;

/**
 * The storage annotation forces the name of the stored field
 */
class Storage_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value               string
	 * @param $reflection_property Reflection_Property
	 */
	public function __construct($value, Reflection_Property $reflection_property)
	{
		if (!$value) {
			$value = $reflection_property->name;
		}
		parent::__construct($value);
	}

}
