<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Method;
use ITRocks\Framework\Reflection\Reflection_Property_Value;

/**
 * Constant or method annotation
 *
 * The value may be a call to a method, or a constant value (text)
 */
class Constant_Or_Method_Annotation extends Method_Annotation
{

	//------------------------------------------------------------------------------------ $is_method
	/**
	 * @var boolean
	 */
	protected bool $is_method;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $value           ?string
	 * @param $class_property  Reflection
	 * @param $annotation_name string
	 */
	public function __construct(?string $value, Reflection $class_property, string $annotation_name)
	{
		parent::__construct($value, $class_property, $annotation_name);
		$value = strval($this->value);

		if (($pos = strpos($value, '::')) !== false) {
			$class_name = substr($value, 0, $pos);
			if ($class_name[0] !== BS) {
				$class_name = BS . $class_name;
			}
			$method_name = substr($value, $pos + 2);
			// value is a method
			if (method_exists($class_name, $method_name)) {
				$this->is_method = true;
				// is the methode accessible ? (public)
				/** @noinspection PhpUnhandledExceptionInspection method_exists */
				if (!(new Reflection_Method($class_name, $method_name))->isPublic()) {
					$this->value = '';
					trigger_error(
						"method $class_name::$method_name is not callable or public. Please review.",
						E_USER_ERROR
					);
				}
			}
			// value is a string to display prefixed by a class name to remove (method_name is the string)
			else {
				$this->is_method = false;
				$this->value     = $method_name;
			}
		}
		// value is a string to display
		else {
			$this->is_method = false;
		}
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * @param $object    object|string|null the object will be the first. string = a class name
	 * @param $arguments array
	 * @return mixed the value returned by the called method
	 */
	public function call(object|string|null $object, array $arguments = []) : mixed
	{
		return $this->is_method ? parent::call($object, $arguments) : Loc::tr($this->value);
	}

	//---------------------------------------------------------------------------------- callProperty
	/**
	 * Calculate value from the object associated to the Reflection_Property, with all common tests :
	 * - only if this annotation has a value
	 * - manage if $property is a Reflection_Property_Value or a simple Reflection_Property
	 *
	 * @param $property  Reflection_Property
	 * @param $arguments array arguments for method call
	 * @return ?string
	 */
	public function callProperty(Reflection_Property $property, array $arguments = []) : ?string
	{
		if ($this->value) {
			$object = ($property instanceof Reflection_Property_Value) ? $property->getObject() : null;
			return $this->call($object, $arguments);
		}
		return null;
	}

	//------------------------------------------------------------------------------------- setMethod
	/**
	 * Change value to method
	 *
	 * @param $callable callable
	 */
	public function setMethod(callable $callable)
	{
		parent::setMethod($callable);
		$this->is_method = true;
	}

	//--------------------------------------------------------------------------------------- setText
	/**
	 * Change value to a text
	 *
	 * @param $text string
	 */
	public function setText(string $text)
	{
		$this->is_method = false;
		$this->value     = $text;
	}

}
