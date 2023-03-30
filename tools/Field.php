<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Reflection\Type;

/**
 * A generic field interface : usefull to define fields, columns and properties
 */
interface Field
{

	//--------------------------------------------------------------------------------------- getName
	/** Gets the field name */
	public function getName() : string;

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Gets the type for the field
	 *
	 * @return Type @values float, integer, string, Date_Time, *
	 */
	public function getType() : Type;

}
