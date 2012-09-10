<?php
namespace SAF\Framework;
use ReflectionClass;
use ReflectionProperty;

require_once "framework/classes/reflection/annotations/Annotation.php";
require_once "framework/classes/reflection/annotations/Annotation_Parser.php";
require_once "framework/classes/reflection/annotations/Annoted.php";
require_once "framework/classes/reflection/Field.php";
require_once "framework/classes/reflection/Reflection_Class.php";
require_once "framework/classes/reflection/Reflection_Method.php";

class Reflection_Property extends ReflectionProperty implements Annoted, Field
{

	//------------------------------------------------------------------------------------------- ALL
	/**
	 * Another constant for default Reflection_Class::getProperties() filter
	 *
	 * @var integer
	 */
	const ALL = 1793;

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Cache Reflection_Property objects for each class and property name
	 *
	 * @var multitype:multitype:Reflection_Property
	 */
	private static $cache = array();

	//------------------------------------------------------------------------------------ $contained
	/**
	 * Cached value of the @contained annotation value
	 *
	 * @var boolean
	 */
	private $contained;

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * Cached value for the doc comment (set by getDocComment() only when $use is true)
	 *
	 * @var string
	 */
	private $doc_comment;

	//-------------------------------------------------------------------------------------- $foreign
	/**
	 * Cached value for the @foreign annotation value
	 *
	 * @var string
	 */
	private $foreign;

	//--------------------------------------------------------------------------------------- $getter
	/**
	 * Cached value for the @getter annotation value
	 *
	 * @var string
	 */
	private $getter;

	//------------------------------------------------------------------------------------ $mandatory
	/**
	 * Cached value for the @mandatory annotation value
	 *
	 * @var boolean
	 */
	private $mandatory;

	//--------------------------------------------------------------------------------------- $setter
	/**
	 * Cached value for the @setter annotation value
	 *
	 * @var string
	 */
	private $setter;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * Cached value for the @type annotation value
	 *
	 * @var string
	 */
	private $type;

	//------------------------------------------------------------------------------------------ $use
	/**
	 * If true, phpdoc must be read directly into php file, as phpDocComment may not be the right one 
	 *
	 * @var boolean
	 */
	private $use;

	//--------------------------------------------------------------------------------- getInstanceOf
	/**
	 * Return Reflection_Property instance for a class name, object, ReflectionClass, Reflection_Class, ReflectionProperty object
	 *
	 * @param string | object | ReflectionClass | ReflectionProperty $of_class
	 * @param string $of_name do not set this if $of_class is a ReflectionProperty
	 * @return Reflection_Property
	 */
	public static function getInstanceOf($of_class, $of_name = null)
	{
		if ($of_class instanceof ReflectionProperty) {
			$of_name  = $of_class->name;
			$of_class = $of_class->class;
		}
		elseif ($of_class instanceof ReflectionClass) {
			$of_class = $of_class->name;
		}
		elseif (is_object($of_class)) {
			$of_class = get_class($of_class); 
		}
		$property = Reflection_Property::$cache[$of_class][$of_name];
		if (!$property) {
			$property = new Reflection_Property($of_class, $of_name);
			Reflection_Property::$cache[$of_class][$of_name] = $property;
		}
		return $property;
	}

	//--------------------------------------------------------------------------------- getAnnotation
	/**
	 * Gets an annotation of the reflected property
	 *
	 * @return string
	 */
	public function getAnnotation($annotation_name)
	{
		return Annotation_Parser::byName($this->getDocComment(), $annotation_name);
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * Gets the declaring class for the reflected property
	 *
	 * @return Reflection_Class
	 */
	public function getDeclaringClass()
	{
		return Reflection_Class::getInstanceOf(parent::getDeclaringClass());
	}

	//--------------------------------------------------------------------------------- getDocComment
	public function getDocComment()
	{
		if ($this->getUse()) {
			if (!isset($this->doc_comment)) {
				$name = $this->name;
				$visibility = $this->isPublic() ? "public" : ($this->isProtected() ? "protected" : "private");
				$doc = file_get_contents($this->getDeclaringClass()->getFileName());
				$doc = substr($doc, 0, strpos($doc, "$visibility \$$name"));
				$i = strrpos($doc, "/**");
				$j = strrpos($doc, "*/", $i) + 2;
				$this->doc_comment = substr($doc, $i, $j - $i);
			}
			return $this->doc_comment;
		}
		return parent::getDocComment();
	}

	//------------------------------------------------------------------------------------ getForeign
	/**
	 * Gets the foreign class property name for the reflected property
	 *
	 * This uses the @foreign annotation.
	 *
	 * @return string
	 */
	public function getForeignName()
	{
		if (!isset($this->foreign)) {
			$foreign = $this->getAnnotation("foreign");
			if (is_object($foreign)) {
				$this->foreign = $foreign->value;
			}
			else {
				$this->foreign = null;
			}
		}
		return $this->foreign;
	}

	//---------------------------------------------------------------------------- getForeignProperty
	/**
	 * Gets the foreign class Reflection_Property for the reflected property
	 *
	 * This uses the @foreign annotation.
	 *
	 * @return Reflection_Property
	 */
	public function getForeignProperty()
	{
		return Reflection_Property::getInstanceOf($this->getType(), $this->getForeignName());
	}

	//------------------------------------------------------------------------------- getGetterMethod
	/**
	 * Gets the getter method associated to the reflected property
	 *
	 * This uses the @getter annotation
	 *
	 * @return Reflection_Method | null
	 */
	public function getGetterMethod()
	{
		$getter_name = $this->getGetterName();
		return $getter_name
			? Reflection_Method::getInstanceOf($this->getDeclaringClass()->name, $getter_name)
			: null;
	}

	//--------------------------------------------------------------------------------- getGetterName
	/**
	 * Gets the getter method name associated to the reflected property
	 *
	 * This uses the @getter annotation
	 *
	 * @return string | null
	 */
	public function getGetterName()
	{
		if (!isset($this->getter)) {
			$getter = $this->getAnnotation("getter");
			if ($getter && $getter->value) {
				$this->getter = $getter->value;
			}
			else {
				$this->getter = null;
			}
		}
		return $this->getter;
	}

	//------------------------------------------------------------------------------- getSetterMethod
	/**
	 * Gets the setter method associated to the reflected property
	 *
	 * This uses the @setter annotation
	 * 
	 * @return Reflection_Method
	 */
	public function getSetterMethod()
	{
		return Reflection_Method::getInstanceOf($this->getDeclaringClass(), $this->getSetterName());
	}

	//--------------------------------------------------------------------------------- getSetterName
	/**
	 * Gets the setter method name associated to the reflected property
	 *
	 * This uses the @setter annotation
	 *
	 * @return string
	 */
	public function getSetterName()
	{
		if (!isset($this->setter)) {
			$setter = $this->getAnnotation("setter");
			if ($setter && $setter->value) {
				$this->setter = $setter->value;
			}
			else {
				$this->setter = null;
			}
		}
		return $this->setter;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Gets the main declared type of the reflected property
	 *
	 * This uses the @var annotation
	 *
	 * @return string
	 */
	public function getType()
	{
		if (!isset($this->type)) {
			$type = $this->getAnnotation("var");
			if (is_object($type) && $type->value) {
				$this->type = $type->value;
			}
			else {
				$types = $this->getDeclaringClass()->getDefaultProperties();
				$this->type = gettype($types[$this->name]);
			}
		}
		return $this->type;
	}

	//---------------------------------------------------------------------------------------- getUse
	/**
	 * Returns true if property doc comment is to be taken in this class instead of parent trait
	 *
	 * This is true when declaring class has a @use $field_name doc comment.
	 *
	 * @return boolean
	 */
	private function getUse()
	{
		if (!isset($this->use)) {
			$use = $this->getDeclaringClass()->getUse();
			$this->use = $use ? in_array($this->name, $use) : false;
		}
		return $this->use;
	}

	//----------------------------------------------------------------------------------- isContained
	/**
	 * Returns true if the reflected property @contained annotation is set 
	 *
	 * @return boolean
	 */
	public function isContained()
	{
		if (!isset($this->contained)) {
			$contained = $this->getAnnotation("contained");
			if (is_object($contained)) {
				$this->contained = $contained->value;
			}
			else {
				$this->contained = false;
			}
		}
		return $this->contained;
	}

	//----------------------------------------------------------------------------------- isMandatory
	/**
	 * Returns true if the reflected property @mandatory annotation is set 
	 *
	 * @return boolean
	 */
		public function isMandatory()
	{
		if (!isset($this->mandatory)) {
			$mandatory = $this->getAnnotation("mandatory");
			if (is_object($mandatory)) {
				$this->mandatory = $mandatory->value;
			}
			else {
				$this->mandatory = false;
			}
		}
		return $this->mandatory;
	}
	
}
