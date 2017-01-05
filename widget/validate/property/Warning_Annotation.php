<?php
namespace ITRocks\Framework\Widget\Validate\Property;

use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use ITRocks\Framework\Widget\Validate;

/**
 * Property @warning annotation for warning-level validation
 */
class Warning_Annotation extends Validate\Annotation\Warning_Annotation
	implements Multiple_Annotation
{
	use Annotation;

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
