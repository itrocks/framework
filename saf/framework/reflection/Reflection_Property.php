<?php
namespace SAF\Framework\Reflection;

use Exception;
use ReflectionProperty;
use SAF\Framework\Reflection\Annotation\Annoted;
use SAF\Framework\Reflection\Annotation\Class_\Override_Annotation;
use SAF\Framework\Reflection\Annotation\Parser;
use SAF\Framework\Reflection\Interfaces;
use SAF\Framework\Reflection\Interfaces\Has_Doc_Comment;
use SAF\Framework\Tools\Date_Time;
use SAF\Framework\Tools\Field;
use SAF\Framework\Tools\Names;

/**
 * A rich extension of the PHP ReflectionProperty class
 */
class Reflection_Property extends ReflectionProperty
	implements Field, Has_Doc_Comment, Interfaces\Reflection_Property
{
	use Annoted;

	const EMPTY_VALUE = '~~EMPTY~VALUE~~';

	//------------------------------------------------------------------------------ $declaring_trait
	/**
	 * Cache for getDeclaringTrait() : please do never use it directly
	 *
	 * @var Reflection_Class
	 */
	private $declaring_trait;

	//---------------------------------------------------------------------------------- $doc_comment
	/**
	 * Cached value for the doc comment (set by getDocComment() only when $use is true)
	 *
	 * @var string
	 */
	private $doc_comment;

	//---------------------------------------------------------------------------------- $final_class
	/**
	 * Final class asked when calling getInstanceOf().
	 * It may not be the class where the property is declared, but the class which was asked.
	 *
	 * @var string
	 */
	public $final_class;

	//-------------------------------------------------------------------------- $overridden_property
	/**
	 * Only if the property is declared into a parent class as well as into the child class.
	 * If not, this will be false.
	 *
	 * @var Reflection_Property|boolean
	 */
	private $overridden_property;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * Full path of the property, if built with getInstanceOf() and a $property.path
	 *
	 * @var string
	 */
	public $path;

	//----------------------------------------------------------------------------------- $root_class
	/**
	 * This is the root class for the path if there is one
	 * This can be null if $this->path does not start from root class and must be ignored into
	 * getValue() and setValue()
	 *
	 * @var string
	 */
	public $root_class;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    string
	 * @param $property_name string
	 */
	public function __construct($class_name, $property_name)
	{
		$this->path       = $property_name;
		$this->root_class = $class_name;
		$i = 0;
		while (($j = strpos($property_name, DOT, $i)) !== false) {
			$property = new Reflection_Property($class_name, substr($property_name, $i, $j - $i));
			$class_name = $property->getType()->getElementTypeAsString();
			$i = $j + 1;
		}
		if ($i) {
			$property_name = substr($property_name, $i);
		}
		$this->final_class = $class_name;
		parent::__construct($class_name, $property_name);
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string The name of the property
	 */
	public function __toString()
	{
		return $this->name;
	}

	//---------------------------------------------------------------------------------------- exists
	/**
	 * @param $class_name    string a class name
	 * @param $property_name string a property name or a property path starting from the class
	 * @return boolean true if the property exists
	 */
	public static function exists($class_name, $property_name)
	{
		if (strpos($property_name, DOT) !== false) {
			$properties_name = explode(DOT, $property_name);
			foreach (array_slice($properties_name, 0, -1) as $property_name) {
				if (!property_exists($class_name, $property_name)) {
					return false;
				}
				$property = new Reflection_Property($class_name, $property_name);
				$class_name = $property->getType()->getElementTypeAsString();
			}
			$property_name = end($properties_name);
		}
		return property_exists($class_name, $property_name);
	}

	//------------------------------------------------------------------------ getAnnotationCachePath
	/**
	 * @return string[]
	 */
	protected function getAnnotationCachePath()
	{
		return [$this->final_class, $this->name];
	}

	//----------------------------------------------------------------------------- getDeclaringClass
	/**
	 * Gets the declaring class for the reflected property
	 *
	 * @return Reflection_Class
	 */
	public function getDeclaringClass()
	{
		return new Reflection_Class($this->class);
	}

	//------------------------------------------------------------------------- getDeclaringClassName
	/**
	 * Gets the declaring class name for the reflected property
	 *
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->class;
	}

	//----------------------------------------------------------------------------- getDeclaringTrait
	/**
	 * Gets the declaring trait for the reflected property
	 * If the property has been declared into a class, this returns this class
	 *
	 * @return Reflection_Class
	 */
	public function getDeclaringTrait()
	{
		if (!isset($this->declaring_trait)) {
			$this->declaring_trait = $this->getDeclaringTraitInternal($this->getDeclaringClass())
				?: $this->getDeclaringClass();
		}
		return $this->declaring_trait;
	}

	//--------------------------------------------------------------------- getDeclaringTraitInternal
	/**
	 * @param $class Reflection_Class
	 * @return Reflection_Class
	 */
	private function getDeclaringTraitInternal(Reflection_Class $class)
	{
		$traits = $class->getTraits();
		foreach ($traits as $trait) {
			$properties = $trait->getProperties();
			if (isset($properties[$this->name])) {
				return $this->getDeclaringTraitInternal($trait) ?: $trait;
			}
		}
		return null;
	}

	//------------------------------------------------------------------------- getDeclaringTraitName
	/**
	 * Gets the declaring trait name for the reflected property
	 * If the property has been declared into a class, this returns this class name
	 *
	 * @return string
	 */
	public function getDeclaringTraitName()
	{
		return $this->getDeclaringTrait()->getName();
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
		$default_method = $this->getAnnotation('default')->value;
		if ($default_method) {
			$default_callable = explode('::', $default_method);
			return call_user_func($default_callable, $this);
		}
		return $this->getDeclaringClass()->getDefaultProperties()[$this->name];
	}

	//--------------------------------------------------------------------------------- getDocComment
	/**
	 * TODO LOW use $flags ?
	 * @param $flags integer[] T_EXTENDS, T_IMPLEMENTS, T_USE
	 * @return string
	 */
	public function getDocComment($flags = [T_USE])
	{
		if (!isset($this->doc_comment)) {
			$overridden_property = $this->getOverriddenProperty();
			$declaring_trait_name = $this->getDeclaringTrait()->name;
			$this->doc_comment =
				$this->getOverrideDocComment()
				. LF . Parser::DOC_COMMENT_IN . $declaring_trait_name . LF
				. parent::getDocComment()
				. LF . Parser::DOC_COMMENT_IN . $declaring_trait_name . LF
				. ((isset($overridden_property)) ? $overridden_property->getDocComment() : '');
		}
		return $this->doc_comment;
	}

	//--------------------------------------------------------------------------------- getEmptyValue
	/**
	 * @return mixed
	 */
	public function getEmptyValue()
	{
		switch ($this->getType()->asString()) {
			case Type::_ARRAY:  return [];
			case Type::BOOLEAN: return false;
			case Type::FLOAT:   return .0;
			case Type::INTEGER: return 0;
			case Type::STRING:  return '';
		}
		return null;
	}

	//--------------------------------------------------------------------------------- getFinalClass
	/**
	 * Gets the final class where the property came from with a call to getProperties()
	 *
	 * @return Reflection_Class
	 */
	public function getFinalClass()
	{
		return new Reflection_Class($this->final_class);
	}

	//----------------------------------------------------------------------------- getFinalClassName
	/**
	 * Gets final class name : the one where the property came from with a call to getProperties()
	 *
	 * @return string
	 */
	public function getFinalClassName()
	{
		return $this->final_class;
	}

	//------------------------------------------------------------------------------ getFinalProperty
	/**
	 * @return Reflection_Property
	 */
	public function getFinalProperty()
	{
		if (strpos($this->path, DOT)) {
			$path = explode(DOT, $this->path);
			$property = new Reflection_Property($this->class, array_shift($path));
			foreach ($path as $property_name) {
				$property = new Reflection_Property(
					$property->getType()->getElementTypeAsString(), $property_name
				);
			}
			return $property;
		}
		return $this;
	}

	//------------------------------------------------------------------------- getOverrideDocComment
	/**
	 * Gets the class @override property doc comment that overrides the original property doc comment
	 *
	 * @return string
	 */
	private function getOverrideDocComment()
	{
		$comment = '';
		foreach (
			(new Reflection_Class($this->final_class))->getListAnnotations('override') as $annotation
		) {
			/** @var $annotation Override_Annotation */
			if ($annotation->property_name === $this->name) {
				$comment .= '/**' . LF;
				foreach ($annotation->values() as $key => $value) {
					$comment .= Parser::DOC_COMMENT_IN . $annotation->class_name . LF;
					$comment .= TAB . SP . '*' . SP . AT . $key . SP . $value . LF;
				}
				$comment .= TAB . SP . '*/';
			}
		}
		return $comment;
	}

	//------------------------------------------------------------------------- getOverriddenProperty
	/**
	 * Gets the parent property overridden by the current one from the parent class
	 *
	 * @return Reflection_Property
	 */
	public function getOverriddenProperty()
	{
		if (!isset($this->overridden_property)) {
			$parent = $this->getDeclaringClass()->getParentClass();
			$this->overridden_property = $parent ? ($parent->getProperty($this->name) ?: false) : false;
		}
		return $this->overridden_property ?: null;
	}

	//----------------------------------------------------------------------------- getParentProperty
	/**
	 * Gets the parent property for a $property.path
	 *
	 * @return Reflection_Property|null
	 */
	public function getParentProperty()
	{
		if (!empty($this->path) && ($i = strrpos($this->path, DOT))) {
			return new Reflection_Property($this->class, substr($this->path, 0, $i));
		}
		return null;
	}

	//--------------------------------------------------------------------------------------- getType
	/**
	 * Gets the type of the property, as defined by its var annotation
	 *
	 * @return Type
	 * @throws Exception
	 */
	public function getType()
	{
		$type = new Type($this->getAnnotation('var')->value);
		if ($type->isNull()) {
			throw new Exception(
				$this->class . '::$' . $this->name . ' type not set using @var annotation',
				E_USER_ERROR
			);
		}
		return $type;
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * Gets value
	 *
	 * @param object $object
	 * @return mixed
	 */
	public function getValue($object = null)
	{
		if (isset($this->root_class) && strpos($this->path, DOT)) {
			$path = explode(DOT, $this->path);
			$property = new Reflection_Property($this->root_class, array_shift($path));
			foreach ($path as $property_name) {
				$object = $property->getValue($object);
				while (is_array($object)) {
					$object = reset($object);
				}
				$property = new Reflection_Property(
					$property->getType()->getElementTypeAsString(), $property_name
				);
			}
			while (is_array($object)) {
				$object = reset($object);
			}
			return $object ? $property->getValue($object) : null;
		}
		else {
			while (is_array($object)) {
				$object = reset($object);
			}
			return $object ? parent::getValue($object) : null;
		}
	}

	//---------------------------------------------------------------------------- isEquivalentObject
	/**
	 * Return true if the both objects match.
	 * If one is an object and the other is an integer, compare $objectX->id with $objectY
	 *
	 * @param $object1 object|integer
	 * @param $object2 object|integer
	 * @return boolean
	 */
	private function isEquivalentObject($object1, $object2)
	{
		if (is_object($object1) && isset($object1->id)) {
			$object1 = $object1->id;
		}
		if (is_object($object2) && isset($object2->id)) {
			$object2 = $object2->id;
		}
		return ($object1 == $object2);
	}

	//------------------------------------------------------------------------- isValueEmptyOrDefault
	/**
	 * Returns true if property is empty or equals to the default value
	 *
	 * Date_Time properties are null if '0000-00-00' or empty date
	 *
	 * @param $value mixed
	 * @return boolean
	 */
	public function isValueEmptyOrDefault($value)
	{
		return empty($value)
			|| $this->isEquivalentObject($value, $this->getDefaultValue())
			|| (($value === '0000-00-00') && $this->getType()->isDateTime())
			|| (($value instanceof Date_Time) && $value->isEmpty());
	}

	//----------------------------------------------------------------------------------- pathAsField
	/**
	 * Returns path formatted as field : uses [] instead of .
	 *
	 * @example if $this->path is 'a.field.path', will return 'a[field][path]'
	 * @return string
	 */
	public function pathAsField()
	{
		return Names::propertyPathToField($this->path ? $this->path : $this->name);
	}

	//-------------------------------------------------------------------------------------- setValue
	/**
	 * Sets value
	 *
	 * @param $object object|mixed object or static property value
	 * @param $value  mixed
	 */
	public function setValue($object, $value = self::EMPTY_VALUE)
	{
		if (isset($this->root_class) && strpos($this->path, DOT)) {
			$path = explode(DOT, $this->path);
			$property = new Reflection_Property($this->root_class, array_shift($path));
			foreach ($path as $property_name) {
				$object = $property->getValue($object);
				$property = new Reflection_Property(
					$property->getType()->getElementTypeAsString(), $property_name
				);
			}
			if ($value === self::EMPTY_VALUE) {
				$property->setValue($object);
			}
			else {
				$property->setValue($object, $value);
			}
		}
		elseif ($value === self::EMPTY_VALUE) {
			parent::setValue($object);
		}
		else {
			parent::setValue($object, $value);
		}
	}

}
