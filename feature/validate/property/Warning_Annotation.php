<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Property @warning annotation for warning-level validation
 */
class Warning_Annotation extends Validate\Annotation\Warning_Annotation
	implements Multiple_Annotation
{
	use Annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           bool|string|null
	 * @param $property        Reflection_Property the contextual Reflection_Property
	 * @param $annotation_name string
	 */
	public function __construct(
		bool|string|null $value, Reflection $property, string $annotation_name = self::ANNOTATION
	) {
		parent::__construct($value, $property, $annotation_name);
		$this->property = $property;
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
		$result        = $this->call($object, [$this->property]);
		$this->message = is_string($result) ? $result : '';
		return is_string($result) ? false : $result;
	}

}
