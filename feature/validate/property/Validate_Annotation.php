<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Property @validate annotation
 */
class Validate_Annotation extends Validate\Annotation\Validate_Annotation
	implements Multiple_Annotation
{
	use Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           ?string
	 * @param $property        Reflection_Property the contextual Reflection_Property
	 * @param $annotation_name string
	 */
	public function __construct(
		?string $value, Reflection $property, string $annotation_name = self::ANNOTATION
	) {
		parent::__construct($value, $property, $annotation_name);
		$this->property = $property;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return Loc::tr('valid value');
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return ?boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate(object $object) : ?bool
	{
		if (!$this->value) {
			return true;
		}
		$result        = $this->call($object, [$this->property]);
		$this->message = is_string($result) ? $result : null;
		return is_string($result) ? false : $result;
	}

}
