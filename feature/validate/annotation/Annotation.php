<?php
namespace ITRocks\Framework\Feature\Validate;

/**
 * Common to all validator annotations classes
 */
trait Annotation
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * The last validated (or not) object
	 *
	 * @var object
	 */
	public $object;

	//---------------------------------------------------------------------------------------- $valid
	/**
	 * True if last validation was positive, else false
	 *
	 * Values are Validate::ERROR, Validate::INFORMATION and Validate::WARNING constant value for read
	 * You can write boolean values true or false too
	 * null value is reserved to invalid validation (should never occur)
	 *
	 * @values Result::const
	 * @var string|boolean simplified boolean values can be returned by Annotation::validate(),
	 *      but they will be changed into Validate::ERROR for true and Validate::INFORMATION for false
	 *      immediately after the internal call to Annotation::validate()
	 */
	public $valid;

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @return string
	 */
	abstract public function reportMessage();

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return boolean true if validated, false if not validated, null if could not be validated
	 */
	abstract public function validate($object);

}
