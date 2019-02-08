<?php
namespace ITRocks\Framework\Feature\Validate\Property;

use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Reflection;
use /** @noinspection PhpUnusedAliasInspection @implements */
	ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Common to all property annotations : includes the property context
 *
 * @extends Reflection\Annotation
 * @implements Property_Context_Annotation
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return mixed
	 */
	public function getPropertyValue()
	{
		$property = $this->property;
		/** @noinspection PhpUnhandledExceptionInspection property is always valid for object */
		return (isset($this->object) && ($property instanceof Reflection\Reflection_Property))
			? $property->getValue($this->object)
			: null;
	}

}
