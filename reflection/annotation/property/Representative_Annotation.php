<?php
namespace ITRocks\Framework\Reflection\Annotation\Property;

use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Annotation\Template\Property_Context_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Representative;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * The 'representative' annotation stores the list of properties which values are representative
 * of the linked object, when we have @link Collection.
 *
 * @example a property called 'name' could be a representative property for a unique named object
 */
class Representative_Annotation extends Representative implements Property_Context_Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'representative';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Builds representative annotation content
	 *
	 * Default representative is empty
	 *
	 * @param $value    ?string
	 * @param $property Reflection_Property
	 */
	public function __construct(?string $value, Reflection_Property $property)
	{
		parent::__construct($value, $property->getType()->asReflectionClass());
		$this->properties = Replaces_Annotations::replaceProperties($this->getProperties());
		$this->value = array_keys($this->properties);
	}

}
