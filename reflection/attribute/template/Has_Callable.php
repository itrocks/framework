<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

use ITRocks\Framework\Reflection\Attribute\Class_\Implement;
use ITRocks\Framework\Reflection\Attribute\Method\Generic;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Method;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Tools\Names;

/**
 * @examples
 * 'method',
 * '$property',
 * 'Class::method',
 * 'Class::$property'
 * [Class::class, 'method']
 * [Class::class, '$property']
 * [$object, 'method']
 * [$object, '$property']
 */
#[Implement(Has_Set_Final::class)]
trait Has_Callable
{

	//------------------------------------------------------------------------------------------ AUTO
	/** The callable member can be AUTO until setFinal() calculates its value from property name */
	protected const AUTO = '¤auto¤';

	//-------------------------------------------------------------------------------- PROPERTY_FIRST
	protected const PROPERTY_FIRST = true;

	//---------------------------------------------------------------------------------------- STATIC
	/** The callable class can be SELF|STATIC until setFinal() calculates its value from class name */
	public const SELF   = 'self';
	public const STATIC = 'static';

	//------------------------------------------------------------------------------------- $callable
	/**
	 * The callable can point on a property name.
	 * The class name can be self::SELF or self::STATIC, which will resolve on call.
	 * Accepts methodName and $property_name
	 *
	 * @noinspection PhpDocFieldTypeMismatchInspection
	 * @var array|callable
	 */
	public array $callable;

	//--------------------------------------------------------------------------------- $generic_name
	public string $generic_name = '';

	//--------------------------------------------------------------------------------------- $static
	public bool $static = false;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(array|callable|string $callable = self::AUTO)
	{
		$this->callable  = is_string($callable)
			? (str_contains($callable, '::') ? explode('::', $callable) : [self::STATIC, $callable])
			: $callable;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return (is_object($this->callable[0]) ? get_class($this->callable[0]) : $this->callable[0])
			. '::' . $this->callable[1];
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * - The $object argument will be the first argument before $arguments in case of a static call
	 * - If the first argument is an Event object, only $arguments will be sent
	 * - If the value is a method for the current object, only $arguments will be sent
	 *
	 * @param $object    object|string|null Context object or class name
	 * @param $arguments array
	 * @return mixed the value returned by the called method
	 */
	public function call(object|string|null $object, array $arguments = []) : mixed
	{
		$class  = $this->static ? $this->callable[0] : $object;
		$member = $this->callable[1];
		if ($class === self::STATIC) {
			$class = get_class($object);
		}
		if (str_starts_with($member, '$')) {
			return $this->static ? $class::$property_name : $class->$member;
		}
		if ($this->generic_name) {
			array_unshift($arguments, $this->generic_name);
		}
		if ($this->static) {
			array_unshift($arguments, $object);
		}
		return call_user_func_array([$class, $member], $arguments);
	}

	//----------------------------------------------------------------------------- defaultMethodName
	protected function defaultMethodName(Reflection_Property $property) : string
	{
		$attribute = Names::classToProperty(trim(rLastParse(get_class($this), BS), '_'));
		return static::PROPERTY_FIRST
			? Names::propertyToMethod($property->getName() . '_' . $attribute)
			: Names::propertyToMethod($attribute . '_' . $property->getName());
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection $reflection) : void
	{
		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		$class_name = ($reflection instanceof Reflection_Class)
			? $reflection->getName()
			: $reflection->getFinalClassName();
		[$class, $member] = $this->callable;
		if (in_array($class, [self::SELF, self::STATIC], true)) {
			$class = $this->callable[0] = $class_name;
		}
		if ($member === self::AUTO) {
			/** @var $reflection Reflection_Property Default value allowed for property attributes only */
			$member = $this->defaultMethodName($reflection);
		}
		/** @var $reflection_member Reflection_Method|Reflection_Property */
		$reflection_member = $reflection::newReflection($class, $member);
		$this->static      = $reflection_member->isStatic();
		if (($reflection_member instanceof Reflection_Method) && Generic::of($reflection_member)) {
			$this->generic_name = $reflection->getName();
		}
	}

}
