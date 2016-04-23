<?php
namespace SAF\Framework\Tools;

use SAF\Framework\Reflection\Type;

/**
 * A generic field interface : usefull to define fields, columns and properties
 */
interface Field
{

	//--------------------------------------------------------------------------------------- getName
	/**
	 * Gets the field name
	 *
	 * @return string
	 */
	public function getName();

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Gets the type for the field
	 *
	 * @return Type
	 * @values float, integer, string, Date_Time, *
	 */
	public function getType();

}
