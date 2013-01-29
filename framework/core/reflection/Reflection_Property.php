<?php
namespace SAF\Framework;
use ReflectionClass;
use ReflectionProperty;

/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/reflection/annotations/Annotation.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/reflection/annotations/Annotation_Parser.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/reflection/annotations/Annoted.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/toolbox/Field.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/reflection/Has_Doc_Comment.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/reflection/Reflection_Class.php";
/** @noinspection PhpIncludeInspection called from index.php */
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
				$property = Reflection_Property::getInstanceOf($of_class, $of_name);
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
	/**
	 * @return Type
	 */
	public function getType()
	{
		$type_string = $this->getAnnotation("var")->value;
		// take only the first type if multiple
		if (($i = strpos($type_string, "|")) !== false) {
			$type_string = substr($type_string, 0, $i);
		}
		$type = new Type($type_string);
		// automatically add current class namespace when not told
		$single = $type->getElementType();
		if ($type->isMultiple()) {
			if ($single->isClass()) {
				$single = new Type(Namespaces::defaultFullClassName($single->asString(), $this->class));
			}
			$type = new Type($single->asString() . substr($type_string, strpos($type_string, "[")));
		}
		elseif ($type->isClass()) {
			$type = new Type(Namespaces::defaultFullClassName($type->asString(), $this->class));
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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string The name of the property
	 */
	public function __toString()
	{
		return $this->name;
	}

}
