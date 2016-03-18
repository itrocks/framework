<?php
namespace SAF\Framework\Reflection\Annotation\Class_;

use SAF\Framework\Reflection\Annotation\Template\Method_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Object_Validator;

/**
 * @validate [[[\Vendor\Module\]Class_Name::]methodName]
 * This is a Multiple_Annotation
 * Tells a method name that will be called by the Validator plugin.
 * This method will be called before an object is written using the Dao.
 */
class Validate_Annotation extends Method_Annotation implements Object_Validator
{

	//-------------------------------------------------------------------------------------- $message
	/**
	 * @var string
	 */
	private $message;

	//--------------------------------------------------------------------------------- reportMessage
	/**
	 * Gets the last validate() call resulting report message
	 *
	 * @return string
	 */
	public function reportMessage()
	{
		return $this->message;
	}

	//-------------------------------------------------------------------------------------- validate
	/**
	 * Validates the property value within this object context
	 *
	 * @param $object object
	 * @return boolean true if validated, false if not validated, null if could not be validated
	 */
	public function validate($object)
	{
		$result = $this->call($object);
		if ($result !== true) {
			$this->message = $result;
		}
		return $result === true;
	}

}
