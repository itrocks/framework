<?php
namespace SAF\Framework;
use ReflectionClass;
use ReflectionProperty;

require_once "framework/core/reflection/annotations/Annotation.php";
require_once "framework/core/reflection/annotations/Annotation_Parser.php";
require_once "framework/core/reflection/annotations/Annoted.php";
require_once "framework/core/toolbox/Field.php";
require_once "framework/core/reflection/Has_Doc_Comment.php";
require_once "framework/core/reflection/Reflection_Class.php";
require_once "framework/core/reflection/Reflection_Method.php";

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

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * Cache Reflection_Property objects for each class and property name
	 *
	 * @var multitype:multitype:Reflection_Property
	 */
	private static $cache = array();

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * Cached value for the doc comment (set by getDocComment() only when $use is true)
	 *
	 * @var string
	 */
	private $doc_comment;

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
		// flexible parameters
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
		// use cache ?
		if (
			isset(self::$cache[$of_class])
			&& isset(self::$cache[$of_class][$of_name])
		) {
			// use cache
			$property = self::$cache[$of_class][$of_name];
		}
		else {
			// no cache : calculate
			$of_name_cache = $of_name;
			$i = 0;
			if (($j = strpos($of_name, ".", $i)) !== false) {
				// $of_name is a "property.path"
				do {
					$property = Reflection_Property::getInstanceOf($of_class, substr($of_name, $i, $j - $i));
					$of_class = $property->getType();
					if ($is_multiple = Type::isMultiple($of_class)) {
						$of_class = Namespaces::fullClassName($is_multiple);
					}
					else {
						$of_class = Namespaces::fullClassName($of_class);
					}
					$i = $j + 1;
				} while (($j = strpos($of_name, ".", $i)) !== false);
				if ($i) {
					$of_name = substr($of_name, $i);
				}
				$property = Reflection_Property::getInstanceOf($of_class, $of_name);
			}
			else {
				// $of_name is a simple property name
				$property = new Reflection_Property($of_class, $of_name);
			}
			self::$cache[$of_class][$of_name_cache] = $property;
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

	//--------------------------------------------------------------------------------- getDocComment
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

	//--------------------------------------------------------------------------------------- getType
	public function getType()
	{
		return $this->getAnnotation("var")->value;
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
			$use = $this->getDeclaringClass()->getAnnotation("use");
			$this->use = $use ? in_array($this->name, $use) : false;
		}
		return $this->use;
	}

}
