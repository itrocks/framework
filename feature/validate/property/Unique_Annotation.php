<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * The unique annotation validator
 */
class Unique_Annotation extends Boolean_Annotation implements Property_Context_Annotation
{
	use Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value    string
	 * @param $property Reflection_Property ie the contextual Reflection_Property object
	 */
	public function __construct($value, Reflection_Property $property)
	{
		parent::__construct($value);
		$this->property = $property;
	}

	//--------------------------------------------------------------------------------- reportMessage
	public function reportMessage(): string
	{
		return 'This value already exist';
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * @param $object object
	 * @return boolean
	 */
	public function validate($object): bool
	{
		$property_name = $this->property->name;
		if (!strlen($object->$property_name)) {
			return true;
		}
		$search = [$property_name => $object->$property_name];
		if (Dao::getObjectIdentifier($object)) {
			$search[] = Func::notEqual($object);
		}
		return !Dao::searchOne($search, get_class($object));
	}

}
