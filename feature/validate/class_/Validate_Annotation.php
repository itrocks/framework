<?php
namespace ITRocks\Framework\Feature\Validate\Class_;

use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Feature\Validate\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;

/**
 * Class @validate annotation
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
	 * @return ?boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate(object $object) : ?bool
	{
		$result        = $this->call($object);
		$this->message = is_string($result) ? $result : null;
		return is_string($result) ? false : $result;
	}

}
