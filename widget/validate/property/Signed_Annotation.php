<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Widget\Validate\Result;

/**
 * The signed annotation validator
 */
class Signed_Annotation extends Boolean_Annotation
{
	use Annotation;

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * @return string
	 */
	public function reportMessage()
	{
		if (is_bool($this->value)) {
			switch ($this->valid) {
				case Result::INFORMATION: return 'number signature is conform';
				case Result::WARNING:     return 'number signature not expected';
				case Result::ERROR:       return 'number signature not allowed';
			}
		}
		return '';
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return boolean
	 */
	public function validate($object)
	{
		if ($this->property instanceof Reflection_Property) {
			if (!$this->value) {
				$value = $this->property->getValue($object);
				return $value > 0;
			}
			return true;
		}
		return null;
	}

}
