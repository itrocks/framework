<?php
namespace ITRocks\Framework\Feature\Cards;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * Card property commons
 */
abstract class Property
{

	//------------------------------------------------------------------------------------- $property
	/**
	 * @var Reflection_Property
	 */
	public Reflection_Property $property;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property Reflection_Property|null
	 */
	public function __construct(Reflection_Property $property = null)
	{
		if (isset($property)) {
			$this->property = $property;
		}
	}

}
