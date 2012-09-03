<?php
namespace Framework;
use ReflectionClass;

class Reflection_Class extends ReflectionClass implements Annoted
{

	private static $cache = array();

	//--------------------------------------------------------------------------------- getInstanceOf
	/**
	 * @param ReflectionClass | string $of_class
	 */
	public static function getInstanceOf($of_class)
	{
		if ($of_class instanceof ReflectionClass) {
			$of_class = $of_class->name;
		}
		$class = Reflection_Class::$cache[$of_class];
		if (!$class) {
			$class = new Reflection_Class($of_class);
			Reflection_Class::$cache[$of_class] = $class;
		}
		return $class;
	}

	//--------------------------------------------------------------------------------- getAnnotation
	/**
	 * @return Annotation
	 */
	public function getAnnotation($annotation_name)
	{
		return Annotation_Parser::byName($this->getDocComment(), $annotation_name);
	}

	//-------------------------------------------------------------------------------- getConstructor
	/**
	 * @return Reflection_Method
	 */
	public function getConstructor()
	{
		return $this->method(parent::getConstructor());
	}

	//-------------------------------------------------------------------------- getDefaultProperties
	/**
	 * @return Reflection_Property
	 */
	public function getDefaultProperties()
	{
		return $this->properties(parent::getDefaultProperties());
	}

	//------------------------------------------------------------------------------------- getMethod
	/**
	 * @return Reflection_Method
	 */
	public function getMethod($name)
	{
		return $this->method(parent::getMethod($name));
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * @return Reflection_Method[]
	 */
	public function getMethods($filter = Reflection_Method::ALL)
	{
		return $this->methods(parent::getMethods($filter));
	}

	//-------------------------------------------------------------------------------- getParentClass
	/**
	 * @return Reflection_Class
	 */
	public function getParentClass()
	{
		return Reflection_Class::getInstanceOf(parent::getParentClass());
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @return Reflection_Property[]
	 */
	public function getProperties($filter = Reflection_Property::ALL)
	{
		return $this->properties(parent::getProperties($filter));
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * @return Reflection_Property
	 */
	public function getProperty($name)
	{
		return $this->property(parent::getProperty($name));
	}

	//--------------------------------------------------------------------------- getStaticProperties
	/**
	 * @return Reflection_Property[]
	 */
	public function getStaticProperties()
	{
		return $this->properties(parent::getStaticProperties());
	}

	//---------------------------------------------------------------------------------------- method
	/**
	 * @return Reflection_Method
	 */
	private function method($method)
	{
		return Reflection_Method::getInstanceOf($method);
	}

	//--------------------------------------------------------------------------------------- methods
	/**
	 * @return Reflection_Method[]
	 */
	private function methods($methods)
	{
		foreach ($methods as $key => $method) {
			$methods[$key] = Reflection_Method::getInstanceOf($method);
		}
		return $methods;
	}

	//------------------------------------------------------------------------------------ properties
	/**
	 * @return Reflection_Property[]
	 */
	private function properties($properties)
	{
		foreach ($properties as $key => $property) {
			$properties[$key] = Reflection_Property::getInstanceOf($property);
		}
		return $properties;
	}

	//-------------------------------------------------------------------------------------- property
	/**
	 * @return Reflection_Property
	 */
	private function property($property)
	{
		return Reflection_Property::getInstanceOf($property);
	}

}
