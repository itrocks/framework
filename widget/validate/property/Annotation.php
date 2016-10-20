<?php
namespace SAF\Framework\Widget\Validate\Property;

use SAF\Framework\Reflection;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;
use SAF\Framework\Widget\Validate;

/**
 * Common to all property annotations : includes the property context
 */
trait Annotation
{
	use Validate\Annotation;

	//------------------------------------------------------------------------------------- $property
	/**
	 * The validated property
	 *
	 * @var Reflection_Property
	 */
	public $property;

	//------------------------------------------------------------------------------ getPropertyValue
	/**
	 * Gets the value of the property from the last validated object
	 *
	 * @return mixed
	 */
	public function getPropertyValue()
	{
		$property = $this->property;
		return (isset($this->object) && ($property instanceof Reflection\Reflection_Property))
			? $property->getValue($this->object)
			: null;
	}

	//--------------------------------------------------------------------------- mandatoryAnnotation
	/**
	 * @return Mandatory_Annotation
	 */
	protected function mandatoryAnnotation()
	{
		return $this->property->getAnnotation('mandatory');
	}

}
