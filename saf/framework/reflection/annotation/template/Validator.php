<?php
namespace SAF\Framework\Reflection\Annotation\Template;

/**
 * A validator annotation implements a validate() method to validate a value into an object context.
 * This is used by the Validator plugin and widget.
 */
interface Validator
{

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @return string
	 */
	public function reportMessage();

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate($object);

}
