<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;
use ITRocks\Framework\Reflection\Attribute\Class_\Implement;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use TypeError;

/**
 * Common to all property annotations : includes the property context
 */
#[Extend(Reflection\Annotation::class), Implement(Property_Context_Annotation::class)]
trait Annotation
{
	use Validate\Annotation;

	//------------------------------------------------------------------------------------- $property
	/**
	 * The validated property
	 *
	 * @var Reflection_Property
	 */
	public Reflection_Property $property;

	//------------------------------------------------------------------------------ getPropertyValue
	/**
	 * Gets the value of the property from the last validated object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnused not_validated.html
	 * @return mixed
	 */
	public function getPropertyValue() : mixed
	{
		$property = $this->property;
		try {
			/** @noinspection PhpUnhandledExceptionInspection property is always valid for object */
			return (isset($this->object) && ($property instanceof Reflection\Reflection_Property))
				? $property->getValue($this->object)
				: null;
		}
		// We could read uninitialized values as validating means object is not guaranteed complete
		catch (TypeError) {
			return null;
		}
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Interfaces\Reflection|Reflection_Property $reflection) : void
	{
		$this->property = $reflection;
		if (method_exists('parent', 'setFinal')) {
			/** @noinspection PhpMultipleClassDeclarationsInspection */
			parent::setFinal($reflection);
		}
	}

}
