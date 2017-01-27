<?php
namespace ITRocks\Framework\Widget\Validate\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Widget\Validate;

/**
 * Property @validate annotation
 */
class Validate_Annotation extends Validate\Annotation\Validate_Annotation
	implements Multiple_Annotation
{
	use Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $property        Reflection|Reflection_Property the contextual Reflection_Property
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $property, $annotation_name = self::ANNOTATION)
	{
		parent::__construct($value, $property, $annotation_name);
		$this->property = $property;
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate($object)
	{
		$result        = $this->call($object, [$this->property]);
		$this->message = is_string($result) ? $result : null;
		return is_string($result) ? false : $result;
	}

}
