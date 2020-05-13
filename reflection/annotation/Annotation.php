<?php
namespace ITRocks\Framework\Reflection;

use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;

/**
 * All annotations classes must inherit from this or any annotation template
 */
class Annotation
{

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = null;

	//----------------------------------------------------------------------------------------- BLOCK
	const BLOCK = 'block';

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Annotation value
	 *
	 * @var string
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Default annotation constructor receive the full doc text content
	 *
	 * Annotation class will have to parse it ie for several parameters or specific syntax, or if they want to store specific typed or calculated value
	 *
	 * @param $value string
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->value);
	}

	//----------------------------------------------------------------------------------------- allOf
	/**
	 * Get all annotations of a reflection object (for multiple annotations)
	 *
	 * @param $reflection_object Reflection
	 * @return static[]
	 * @see Annoted::getAnnotation
	 */
	public static function allOf(Reflection $reflection_object)
	{
		return $reflection_object->getAnnotations(
			static::ANNOTATION ?: strtolower(lLastParse(static::class, '_Annotation'))
		);
	}

	//--------------------------------------------------------------------- classNameToAnnotationName
	/**
	 * @param $class_name string
	 * @return string
	 */
	private static function classNameToAnnotationName($class_name)
	{
		return Names::classToDisplay(
			lLastParse(Namespaces::shortClassName($class_name), '_Annotation')
		);
	}

	//---------------------------------------------------------------------------------------- equals
	/**
	 * @param $reflection_object       Reflection
	 * @param $other_reflection_object Reflection
	 * @return boolean
	 */
	public static function equals(Reflection $reflection_object, Reflection $other_reflection_object)
	{
		return (static::of($reflection_object)->value === static::of($other_reflection_object)->value);
	}

	//----------------------------------------------------------------------------- getAnnotationName
	/**
	 * Gets annotation name (the displayable root of the annotation class name, when set)
	 *
	 * @return string
	 */
	public function getAnnotationName()
	{
		return self::classNameToAnnotationName(get_class($this));
	}

	//----------------------------------------------------------------------------------------- local
	/**
	 * Returns a value local to $reflection_object for the annotation
	 *
	 * @param $reflection_object Reflection
	 * @return static
	 * @see Annoted::setAnnotationLocal
	 */
	public static function local(Reflection $reflection_object)
	{
		return $reflection_object->setAnnotationLocal(
			static::ANNOTATION ?: self::classNameToAnnotationName(static::class)
		);
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param $reflection_object Reflection
	 * @return static
	 * @see Annoted::getAnnotation
	 */
	public static function of(Reflection $reflection_object)
	{
		return $reflection_object->getAnnotation(
			static::ANNOTATION ?: self::classNameToAnnotationName(static::class)
		);
	}

	//-------------------------------------------------------------------------------------- setLocal
	/**
	 * @deprecated
	 * @param $reflection_object Reflection
	 * @return static
	 */
	public static function setLocal(Reflection $reflection_object)
	{
		return $reflection_object->setAnnotationLocal(
			static::ANNOTATION ?: self::classNameToAnnotationName(static::class)
		);
	}

}
