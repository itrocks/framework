<?php
namespace SAF\Framework;

class Dataset_Annotation extends Annotation implements Reflection_Object_Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $value
	 * @param Reflection_Class $reflection_object
	 */
	public function __construct($value, $reflection_object)
	{
		parent::__construct($value);
		if (!$this->value) {
			$this->value = Names::classToSet($reflection_object->name);
		}
	}

}
