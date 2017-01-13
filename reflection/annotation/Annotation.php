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

	//----------------------------------------------------------------------------- getAnnotationName
	/**
	 * Gets annotation name (the displayable root of the annotation class name, when set)
	 *
	 * @return string
	 */
	public function getAnnotationName()
	{
		return Names::classToDisplay(
			lLastParse(Namespaces::shortClassName(get_class($this)), '_Annotation')
		);
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
			static::ANNOTATION ?: strtolower(lLastParse(static::class, '_Annotation'))
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
			static::ANNOTATION ?: strtolower(lLastParse(static::class, '_Annotation'))
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
			static::ANNOTATION ?: strtolower(lLastParse(static::class, '_Annotation'))
		);
	}

}
