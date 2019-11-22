<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Event;
use ITRocks\Framework\PHP\Dependency;
use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Method;
use ITRocks\Framework\Tools\Names;
use ReflectionException;

/**
 * This annotation template contains a callable method :
 * - "methodName" to call self::methodName()
 * - "Class_Name::methodName" to call My\Namespace\Class_Name::methodName()
 * - "\Another\Namespace\Class_Name::methodName" to call Another\Namespace\Class_Name::methodName()
 * - "Outside_Class_Name::methodName()" to call Another\Namespace\Outside_Class_Name::methodName()
 *   needs a use clause to be defined like this : "use Another\Namespace\Outside_Class_Name;"
 *
 * Used by Property\Default_Annotation, Property\Getter_Annotation, Property\Setter_Annotation
 */
class Method_Annotation extends Annotation implements Reflection_Context_Annotation
{

	//--------------------------------------------------------------------------------- $is_composite
	/**
	 * @var boolean
	 */
	public $is_composite = false;

	//--------------------------------------------------------------------------------------- $static
	/**
	 * @var boolean
	 */
	public $static = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value           string
	 * @param $class_property  Reflection
	 * @param $annotation_name string
	 */
	public function __construct($value, Reflection $class_property, $annotation_name)
	{
		if (!empty($value)) {
			$value = $this->completeValue($value, $class_property, $annotation_name);
		}
		parent::__construct($value);
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * - The $object argument will be the first argument before $arguments in case of a static call
	 * - If the first argument is an Event object, only $arguments will be sent
	 * - If the value is a method for the current object, only $arguments will be sent
	 *
	 * @param $object    object|string the object will be the first. If string, this is a class name
	 * @param $arguments array
	 * @return mixed the value returned by the called method
	 */
	public function call($object, array $arguments = [])
	{
		if ($this->static || is_string($object)) {
			if (($object !== $this->value) && !(reset($arguments) instanceof Event)) {
				array_unshift($arguments, $object);
			}
			return call_user_func_array($this->value, $arguments);
		}
		return call_user_func_array([$object, rParse($this->value, '::')], $arguments);
	}

	//--------------------------------------------------------------------------------- completeValue
	/**
	 * @param $value           string
	 * @param $class_property  Reflection
	 * @param $annotation_name string
	 * @return string
	 */
	protected function completeValue($value, Reflection $class_property, $annotation_name)
	{
		if ($class_property instanceof Reflection_Property) {
			$class = (
				($class_property instanceof Framework\Reflection\Reflection_Property)
				&& strpos($class_property->path, DOT)
			)
				? $class_property->getRootClass()
				: $class_property->getFinalClass();
		}
		else {
			$class = $class_property;
		}
		if ($pos = strpos($value, '::')) {
			$type_annotation = new Type_Annotation(substr($value, 0, $pos));
			if (in_array($type_annotation->value, ['__CLASS_NAME__', 'self'])) {
				$type_annotation->value = BS . $class->getName();
			}
			elseif ($type_annotation->value == 'static') {
				if ($class_property instanceof Reflection_Property) {
					$class = $class_property->getDeclaringClass();
				}
				$type_annotation->value = BS . $class->getName();
			}
			elseif ($type_annotation->value == 'composite') {
				/** @var $composite_property Reflection_Property */
				$composite_property     = call_user_func([$class->getName(), 'getCompositeProperty']);
				$type_annotation->value = $composite_property->getType()->asString();
				$this->is_composite     = true;
			}
			// if the property is declared into the final class : try using the class namespace name
			if (
				!$this->is_composite
				&& (
					!($class_property instanceof Reflection_Property)
					|| ($class_property->getDeclaringTraitName() === $class_property->getFinalClassName())
				)
			) {
				$dependencies = Dao::search(
					['class_name' => $class->getName(), 'type' => Dependency::T_NAMESPACE_USE],
					Dependency::class
				);
				$use = [];
				foreach ($dependencies as $dependency) {
					$use[] = $dependency->dependency_name;
				}
				$type_annotation->applyNamespace($class->getNamespaceName(), $use);
			}
			if (!class_exists($type_annotation->value)) {
				$this->searchIntoDeclaringTrait($class_property, $type_annotation, $value, $pos);
			}
			if (!class_exists($type_annotation->value)) {
				$this->searchIntoFinalClass($class_property, $type_annotation, $value, $pos);
			}
			if (!class_exists($type_annotation->value) && !trait_exists($type_annotation->value)) {
				trigger_error(
					sprintf(
						'Not found full class name for Method_Annotation %s value %s class %s property %s',
						$annotation_name, $value, $class->getName(), $class_property->getName()
					),
					E_USER_ERROR
				);
			}
			$value = $type_annotation->value . substr($value, $pos);
			$this->static = true;
		}
		else {
			if ($value === true) {
				$value = Names::propertyToMethod($annotation_name);
			}
			$value = (substr($value, 0, 1) === SL)
				? substr($value, 1)
				: ($class->getName() . '::' . $value);
		}
		return $value;
	}

	//--------------------------------------------------------------------------- getReflectionMethod
	/**
	 * @return Reflection_Method
	 * @throws ReflectionException
	 */
	public function getReflectionMethod()
	{
		[$class, $method] = explode('::', $this->value);
		return new Reflection_Method($class, $method);
	}

	//---------------------------------------------------------------------- searchIntoDeclaringTrait
	/**
	 * Search using the property declaring trait namespace and uses
	 *
	 * @param $class_property  Reflection
	 * @param $type_annotation Type_Annotation
	 * @param $value           string
	 * @param $pos             integer
	 */
	private function searchIntoDeclaringTrait(
		Reflection $class_property, Type_Annotation $type_annotation, $value, $pos
	) {
		if ($class_property instanceof Reflection_Property) {
			$php_class = Reflection_Class::of($class_property->getDeclaringTraitName());
			$type_annotation->value = substr($value, 0, $pos);
			$type_annotation->applyNamespace(
				$php_class->getNamespaceName(), $php_class->getNamespaceUse()
			);
		}
	}

	//-------------------------------------------------------------------------- searchIntoFinalClass
	/**
	 * TODO property you'd better do this into the last @override field @$annotation_name class
	 *
	 * @param $class_property  Reflection
	 * @param $type_annotation Type_Annotation
	 * @param $value           string
	 * @param $pos             integer
	 */
	private function searchIntoFinalClass(
		Reflection $class_property, Type_Annotation $type_annotation, $value, $pos
	) {
		$class = ($class_property instanceof Reflection_Property)
			? $class_property->getFinalClass()
			: $class_property;
		/*
		// TODO commented, but should be activated to finalize this (launch unit tests)
		trigger_error(
			sprintf(
				'Looking namespace use for Method_Annotation into final class %1 for property %2'
					. ' is not reliable',
				$class->getName(), $class_property->getName()
			),
			E_USER_WARNING
		);
		*/
		$php_class = Reflection_Class::of($class->getName());
		$type_annotation->value = substr($value, 0, $pos);
		$type_annotation->applyNamespace($class->getNamespaceName(), $php_class->getNamespaceUse());
	}

	//------------------------------------------------------------------------------------- setMethod
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $callable callable
	 */
	public function setMethod(callable $callable)
	{
		/** @noinspection PhpUnhandledExceptionInspection callable must be valid */
		$this->static = (new Reflection_Method($callable[0], $callable[1]))->isStatic();
		$this->value  = $callable;
	}

}
