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
	 * @var foreign
	 */
	private $foreign;

	/**
	 * @var string
	 */
	private $getter;

	/**
	 * @var boolean
	 */
	private $mandatory;

	/**
	 * @var string
	 */
	private $setter;

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
	/**
	 * @return string
	 */
	public function getForeignName()
	{
		if (!isset($this->foreign)) {
			$foreign = $this->getAnnotation("foreign");
			if (is_object($foreign)) {
				$this->foreign = $foreign->value;
			} else {
				$this->foreign = null;
			}
		}
		return $this->foreign;
	}

	//---------------------------------------------------------------------------- getForeignProperty
	/**
	 * @return Reflection_Property
	 */
	public function getForeignProperty()
	{
		return Reflection_Property::getInstanceOf($this->getType(), $this->getForeignName());
	}

	//------------------------------------------------------------------------------------- getGetter
	/**
	 * @return Reflection_Method
	 */
	public function getGetter()
	{
		return Reflection_Method::getInstanceOf($this->getDeclaringClass(), $this->getGetterName());
	}

	//--------------------------------------------------------------------------------- getGetterName
	/**
	 * @return string
	 */
	public function getGetterName()
	{
		if (!isset($this->getter)) {
			$getter = $this->getAnnotation("getter");
			if ($getter && $getter->value) {
				$this->getter = $getter->value;
			} else {
				$getter = Names::propertyToMethod($this->name, "get");
				if ($this->getDeclaringClass()->hasMethod($getter)) {
					$this->getter = $getter;
				} else {
					$this->getter = null;
				}
			}
		}
		return $this->getter;
	}

	//------------------------------------------------------------------------------------- getSetter
	/**
	 * @return Reflection_Method
	 */
	public function getSetter()
	{
		return Reflection_Method::getInstanceOf($this->getDeclaringClass(), $this->getSetterName());
	}

	//--------------------------------------------------------------------------------- getSetterName
	/**
	 * @return string
	 */
	public function getSetterName()
	{
		if (!isset($this->setter)) {
			$setter = $this->getAnnotation("setter");
			if ($setter && $setter->value) {
				$this->setter = $setter->value;
			} else {
				$setter = Names::propertyToMethod($this->name, "set");
				if ($this->getDeclaringClass()->hasMethod($setter)) {
					$this->setter = $setter;
				} else {
					$this->setter = null;
				}
			}
		}
		return $this->setter;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return string
	 */
	public function getType()
	{
		if (!isset($this->type)) {
			$type = $this->getAnnotation("var");
			if (is_object($type) && $type->value) {
				$this->type = $type->value;
			} else {
				$types = $this->getDeclaringClass()->getDefaultProperties();
				$this->type = gettype($types[$this->name]);
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
		if (!isset($this->mandatory)) {
			$mandatory = $this->getAnnotation("mandatory");
			if (is_object($mandatory)) {
				$this->mandatory = $mandatory->value;
			} else {
				$this->mandatory = false;
			}
		}
		return $this->mandatory;
	}
	
}
