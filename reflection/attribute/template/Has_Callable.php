<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

use ITRocks\Framework\Dao\Event;
use ITRocks\Framework\Reflection\Attribute\Class_\Implement;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

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
#[Implement(Has_Set_Declaring::class, Has_Set_Final::class)]
trait Has_Callable
{

	//------------------------------------------------------------------------- call method CONSTANTS
	public const SELF   = 'self';
	public const STATIC = 'static';

	//--------------------------------------------------------------------------------------- $static
	public bool $static;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * The callable can point on a property name.
	 * The class name can be self::SELF or self::STATIC, which will resolve on call.
	 *
	 * @noinspection PhpDocFieldTypeMismatchInspection
	 * @var array|callable
	 */
	public array $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(array|callable|string $value, bool $static = false)
	{
		$this->static = $static;
		$this->value  = is_string($value)
			? (str_contains($value, '::') ? explode('::', $value) : [self::STATIC, $value])
			: $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		$context = is_object($this->value[0]) ? get_class($this->value[0]) : $this->value[0];
		return $context . '::' . $this->value[1];
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
		$context = ($object && !$this->static) ? $object : $this->value[0];
		if (str_starts_with($property_name = $this->value[1], '$')) {
			try {
				return ((new ReflectionProperty($context, $property_name))->isStatic())
					? $context::$property_name
					: $context->$property_name;
			}
			catch (ReflectionException) {
				return null;
			}
		}
		if (($context !== $object) && !(reset($arguments) instanceof Event)) {
			try {
				$method     = new ReflectionMethod($this->value);
				$parameters = $method->getParameters();
				$parameter  = reset($parameters) ?: null;
				$type       = $parameter?->getType()?->getName();
				if ($type === self::SELF) {
					$type = $method->getDeclaringClass()->name;
				}
				elseif ($type === self::STATIC) {
					$type = $method->class;
				}
				if ($type && is_a($object, $type, true)) {
					array_unshift($arguments, $object);
				}
			}
			catch (ReflectionException) {
			}
		}
		return call_user_func_array([$context, $this->value[1]], $arguments);
	}

	//---------------------------------------------------------------------------------- setDeclaring
	public function setDeclaring(Reflection $reflection) : void
	{
		if ($this->static || !$this->value || ($this->value[0] !== self::SELF)) {
			return;
		}
		/** @noinspection PhpPossiblePolymorphicInvocationInspection All but Reflection_Class have
		 * getDeclaringClassName */
		$this->value[0] = ($reflection instanceof Reflection_Class)
			? $reflection->getName()
			: $reflection->getDeclaringClassName();
	}

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection $reflection) : void
	{
		if ($this->static || !$this->value || ($this->value[0] !== self::STATIC)) {
			return;
		}
		/** @noinspection PhpPossiblePolymorphicInvocationInspection All but Reflection_Class have
		 * getFinalClassName */
		$this->value[0] = ($reflection instanceof Reflection_Class)
			? $reflection->getName()
			: $reflection->getFinalClassName();
	}

}
