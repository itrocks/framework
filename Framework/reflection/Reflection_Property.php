<?php

class Reflection_Property extends ReflectionProperty implements Annoted
{

	/**
	 * @var integer
	 */
	const ALL = 1793;

	/**
	 * @var array
	 */
	private static $cache = array();

	/**
	 * @var boolean
	 */
	private $mandatory;

	/**
	 * @var string
	 */
	private $type;

	//--------------------------------------------------------------------------------- getInstanceOf
	/**
	 * @param ReflectionProperty | string $of_class
	 * @param string                      $of_name only if $of_class is a string too
	 */
	public static function getInstanceOf($of_class, $of_name = null)
	{
		if ($of_class instanceof ReflectionProperty) {
			$of_name  = $of_class->name;
			$of_class = $of_class->class;
		}
		$field = Reflection_Property::$cache[$of_class][$of_name];
		if (!$field) {
			$field = new Reflection_Property($of_class, $of_name);
			Reflection_Property::$cache[$of_class][$of_name] = $field;
		}
		return $field;
	}

	//--------------------------------------------------------------------------------- getAnnotation
	/**
	 * @return string
	 */
	public function getAnnotation($annotation_name)
	{
		return Annotation_Parser::byName($this->getDocComment(), $annotation_name);
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * @return Reflection_Class
	 */
	public function getDeclaringClass()
	{
		return Reflection_Class::getInstanceOf(parent::getDeclaringClass());
	}

	//------------------------------------------------------------------------------------ getForeign
	public function getForeignName()
	{
		return $this->getAnnotation("foreign")->value;
	}

	//---------------------------------------------------------------------------- getForeignProperty
	public function getForeignProperty()
	{
		return Reflection_Property::getInstanceOf($this->getType(), $this->getForeignName());
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return string
	 */
	public function getType()
	{
		if (!$this->type) {
			$this->type = $this->getAnnotation("var")->value;
			if (!$this->type) {
				$types = $this->getDeclaringClass()->getDefaultProperties();
				$this->type = gettype($types[$this->getName()]);
			}
		}
		return $this->type;
	}

	//----------------------------------------------------------------------------------- isMandatory
	/**
	 * @return boolean
	 */
	public function isMandatory()
	{
		if (!is_bool($this->mandatory)) {
			$this->mandatory = $this->getAnnotation("mandatory")->value;
		}
		return $this->mandatory;
	}
	
}
