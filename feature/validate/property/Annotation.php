<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use TypeError;

/**
 * Common to all property annotations : includes the property context
 *
 * @implements Property_Context_Annotation
 */
#[Extends_(Reflection\Annotation::class)]
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

}
