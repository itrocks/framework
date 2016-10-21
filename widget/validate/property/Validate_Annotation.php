<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use SAF\Framework\Widget\Validate;

/**
 * Property @validate annotation
 */
class Validate_Annotation extends Validate\Annotation\Validate_Annotation
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
		$result = $this->call($object, [$this->property]);
		$this->message = is_string($result) ? $result : null;
		return is_string($result) ? false : $result;
	}

}
