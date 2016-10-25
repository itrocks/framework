<?php

namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use SAF\Framework\Widget\Validate;

/**
 * Property @validate annotation
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
	 * @return string Message if has warning, true else
	 */
	public function validate($object)
	{
		return $this->checkCallReturn($this->call($object, [$this->property]));
	}

}
