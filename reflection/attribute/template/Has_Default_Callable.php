<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Tools\Names;
use ReflectionException;

trait Has_Default_Callable
{

	//------------------------------------------------------------------------------------- $callable
	/**
	 * - If empty, then there is no setter
	 * - The first element is the name of the class, or an empty string for $this|static
	 *
	 * @noinspection PhpDocFieldTypeMismatchInspection
	 * @var callable
	 */
	public array $callable;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $callable array|string
	 */
	public function __construct(array|string $callable = '')
	{
		if (is_string($callable)) {
			$callable = $callable ? ['', $callable] : [];
		}
		$this->callable = $callable;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return join('::', $this->callable);
	}

	//------------------------------------------------------------------------------ getDefaultMethod
	/**
	 * @param $prefix     string
	 * @param $reflection Reflection|Reflection_Property
	 * @throws ReflectionException
	 */
	protected function getDefaultMethod(
		string $prefix, Reflection|Reflection_Property $reflection
	) : void
	{
		if (!$this->callable) {
			$this->callable = ['', $prefix . ucfirst(Names::propertyToMethod($reflection->getName()))];
		}
		if ($this->callable[0]) {
			return;
		}
		$class   = $reflection->getFinalClass();
		$methods = $class->getMethods([T_EXTENDS, T_IMPLEMENTS, T_USE]);
		$method  = $methods[$this->callable[1]] ?? null;
		if ($method) {
			$this->callable[0] = $method->isStatic() ? 'static::' : '$this->';
			return;
		}
		$class_name    = $class->getName();
		$method_name   = $this->callable[1];
		$property_name = $reflection->getName();
		throw new ReflectionException(
			"Missing $class_name::\$$property_name $prefix $class_name::$method_name()"
		);
	}

}
