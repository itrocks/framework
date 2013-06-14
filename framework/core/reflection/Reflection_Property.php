<?php
namespace SAF\Framework;

use Exception;
use ReflectionClass;
use ReflectionProperty;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/annotations/Annotation.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/annotations/Annotation_Parser.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/annotations/Annoted.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Field.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/Has_Doc_Comment.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/Reflection_Class.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/Reflection_Method.php";

/**
 * A rich extension of the PHP ReflectionProperty class, adding :
 * - annotations management
 * - getType() method to get the real type of data stored into the property
 * - getUse() method to help working with overriden properties
 */
class Reflection_Property extends ReflectionProperty implements Field, Has_Doc_Comment
{
	use Annoted;

	//------------------------------------------------------------------------------------------- ALL
	/**
	 * Another constant for default Reflection_Class::getProperties() filter
	 *
	 * @var integer
	 */
	const ALL = 1793;

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * Cached value for the doc comment (set by getDocComment() only when $use is true)
	 *
	 * @var string
	 */
	private $doc_comment;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * Full path of the property, if built with getInstanceOf() and a $property.path
	 *
	 * @var string
	 */
	public $path;

	//------------------------------------------------------------------------------------------ $use
	/**
	 * If true, phpdoc must be read directly into php file, as phpDocComment may not be the right one
	 *
	 * @var boolean
	 */
	private $use;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string The name of the property
	 */
	public function __toString()
	{
		return $this->name;
	}

	//--------------------------------------------------------------------------------- getInstanceOf
	/**
	 * Gets the Reflection_Property instance
	 *
	 * @param $of_class string|object|Reflection_Class|Reflection_Property|ReflectionClass|ReflectionProperty|Type
	 * @param $of_name  string $of_name do not set this if is a ReflectionProperty
	 * @return Reflection_Property
	 */
	public static function getInstanceOf($of_class, $of_name = null)
	{
		/** @var Reflection_Property[] */
		static $cache = array();
		// flexible parameters
		if ($of_class instanceof Type) {
			$of_class = $of_class->asString();
		}
		elseif ($of_class instanceof ReflectionProperty) {
			$of_name  = $of_class->name;
			$of_class = $of_class->class;
		}
		elseif ($of_class instanceof ReflectionClass) {
			$of_class = $of_class->name;
		}
		elseif (is_object($of_class)) {
			$of_class = get_class($of_class);
		}
		// use cache ?
		if (isset($cache[$of_class]) && isset($cache[$of_class][$of_name])) {
			// use cache
			$property = $cache[$of_class][$of_name];
		}
		else {
			// no cache : calculate
			$of_name_cache = $of_name;
			$i = 0;
			if (($j = strpos($of_name, ".", $i)) !== false) {
				// $of_name is a "property.path"
				do {
					$property = Reflection_Property::getInstanceOf($of_class, substr($of_name, $i, $j - $i));
					$of_class = $property->getType()->getElementTypeAsString();
					$i = $j + 1;
				} while (($j = strpos($of_name, ".", $i)) !== false);
				if ($i) {
					$of_name = substr($of_name, $i);
				}
				$property = new Reflection_Property($of_class, $of_name);
				$property->path = $of_name_cache;
			}
			else {
				// $of_name is a simple property name
				$property = new Reflection_Property($of_class, $of_name);
			}
			$cache[$of_class][$of_name_cache] = $property;
		}
		return $property;
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

	//----------------------------------------------------------------------------- getDeclaringTrait
	/**
	 * Gets the real declaring trait (or class if declared in class) of a property
	 *
	 * @return Reflection_Class
	 */
	public function getDeclaringTrait()
	{
		foreach ($this->getDeclaringClass()->getTraits() as $trait) {
			$properties = $trait->getProperties();
			if (isset($properties[$this->name])) {
				$property = $properties[$this->name];
				$declaring_trait = $property->getDeclaringTrait();
				return isset($declaring_trait) ? $declaring_trait : $property->getDeclaringClass();
			}
		}
		return $this->getDeclaringClass();
	}

	//------------------------------------------------------------------------------- getDefaultValue
	/**
	 * Gets the default value for the property
	 *
	 * This is not optimized and could be slower than getting the class's default values one time
	 *
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		return $this->getDeclaringClass()->getDefaultProperties()[$this->name];
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * @param $get_use boolean
	 * @return string
	 */
	public function getDocComment($get_use = true)
	{
		if ($get_use && $this->getUse()) {
			if (!is_string($this->doc_comment)) {
				$name = $this->name;
				$visibility = $this->isPublic()
					? "public"
					: ($this->isProtected() ? "protected" : "private");
				$doc = file_get_contents($this->getDeclaringClass()->getFileName());
				$doc = substr($doc, 0, strpos($doc, "$visibility \$$name"));
				$i = strrpos($doc, "/**");
				$j = strrpos($doc, "*/", $i) + 2;
				$this->doc_comment = substr($doc, $i, $j - $i) . parent::getDocComment();
			}
			return $this->doc_comment;
		}
		return parent::getDocComment();
	}

	//----------------------------------------------------------------------------- getParentProperty
	/**
	 * Gets the parent property for a $property.path
	 *
	 * @return Reflection_Property|null
	 */
	public function getParentProperty()
	{
		if (!empty($this->path) && ($i = strrpos($this->path, "."))) {
			return self::getInstanceOf(substr($this->path, 0, $i));
		}
		return null;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * @return Type
	 * @throws Exception
	 */
	public function getType()
	{
		$type_string = $this->getAnnotation("var")->value;
		$type = new Type($type_string);
		// automatically add current class namespace
		if ($type->isClass()) {
			$element_class_name = $type->getElementTypeAsString();
			if (Namespaces::isShortClassName($element_class_name)) {
				$declaring_trait = $this->getDeclaringTrait()->name;
				$class_name = Namespaces::defaultFullClassName($element_class_name, $declaring_trait);
				$type = $type->isMultiple()
					? (new Type($class_name, $type->canBeNull()))->multiple()
					: new Type($class_name, $type->canBeNull());
			}
		}
		if ($type->isNull()) {
			throw new Exception(
				$this->class . '::$' . $this->name . " type not set using @var annotation",
				E_USER_ERROR
			);
		}
		return $type;
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
			foreach ($this->getDeclaringClass()->getListAnnotations("use") as $use) {
				if (in_array($this->name, $use->values())) {
					$this->use = true;
					break;
				}
			}
		}
		return $this->use;
	}

	//------------------------------------------------------------------------- isValueEmptyOrDefault
	/**
	 * Returns true if property is empty or equals to the default value
	 *
	 * Date_Time properties are null if "0000-00-00" or empty date
	 *
	 * @param $value mixed
	 * @return boolean
	 */
	public function isValueEmptyOrDefault($value)
	{
		return empty($value)
			|| ($value === $this->getDefaultValue())
			|| (($value === "0000-00-00") && $this->getType()->isDateTime())
			|| (($value instanceof Date_Time) && $value->isEmpty());
	}

}
