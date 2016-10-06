<?php
namespace SAF\Framework\Widget\Validate\Template;

use SAF\Framework\Reflection\Annotation\Template\Method_Annotation;
use SAF\Framework\Reflection\Annotation\Template\Validator;
use SAF\Framework\Reflection\Interfaces\Reflection;
use SAF\Framework\Reflection\Interfaces\Reflection_Property;
use SAF\Framework\Widget\Validate\Property\Property_Validate_Annotation;

/**
 * @validate [[[\Vendor\Module\]Class_Name::]methodName]
 * This is a Multiple_Annotation
 * Tells a method name that will be called by the Validator plugin.
 * This method will be called before an object is written using the Dao.
 * If annotation is set on a class, arguments will be only the object
 * If annotation is set on a property, arguments will be the object then the Reflection_Property
 */
class Validate_Annotation extends Method_Annotation implements Validator
{
	use Property_Validate_Annotation {
		Property_Validate_Annotation::getValue as getPropertyValue;
	}

	//-------------------------------------------------------------------------------------- $message
	/**
	 * @var string
	 */
	private $message;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $class_property  Reflection
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $class_property, $annotation_name)
	{
		parent::__construct($value, $class_property, $annotation_name);
		if ($class_property instanceof Reflection_Property) {
			$this->property = $class_property;
		}
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets the value of the property from the last validated object
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		if (isset($this->property)) {
			$return = $this->getPropertyValue();
			return $return;
		}
		return null;
	}

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
		$this->object = $object;
		$this->valid = $this->call($object, isset($this->property) ? [$this->property] : []);
		if ($this->valid !== true && $this->valid !== false && !is_null($this->valid)) {
			$this->message = $this->valid;
			$this->valid = false;
		}
		return $this->valid === true;
	}

}
