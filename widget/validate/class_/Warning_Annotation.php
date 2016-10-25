<?php

namespace SAF\Framework\Widget\Validate\Class_;

use SAF\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use SAF\Framework\Widget\Validate;
use SAF\Framework\Widget\Validate\Annotation;

/**
 * Property @validate annotation
 */
class Warning_Annotation extends Validate\Annotation\Warning_Annotation
	implements Multiple_Annotation
{

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate($object)
	{
		return $this->checkCallReturn($this->call($object));
	}

}
