<?php
namespace ITRocks\Framework\Reflection\Interfaces;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;

/**
 * Common interface for reflection objects
 */
interface Reflection
{

	//--------------------------------------------------------------------------------- getAnnotation
	/** Gets a single annotation of the reflected property */
	public function getAnnotation(string $annotation_name) : Annotation;

	//-------------------------------------------------------------------------------- getAnnotations
	/**
	 * Gets multiple annotations of the reflected property
	 *
	 * If the annotation name is given, will return the Annotation[]
	 * If no annotation name is given, all annotations will be read for the reflected property
	 *
	 * @return Annotation[]|Multiple_Annotation[]
	 */
	public function getAnnotations(string $annotation_name = '') : array;

	//----------------------------------------------------------------------------- getListAnnotation
	/** Gets a List_Annotation for the reflected property */
	public function getListAnnotation(string $annotation_name) : List_Annotation;

	//--------------------------------------------------------------------------------------- getName
	public function getName() : ?string;

	//--------------------------------------------------------------------------------- newReflection
	public static function newReflection(string $class, string $member = null) : Reflection;

	//---------------------------------------------------------------------------- newReflectionClass
	public static function newReflectionClass(string $class) : Reflection_Class;

	//--------------------------------------------------------------------------- newReflectionMethod
	public static function newReflectionMethod(string $class, string $method) : Reflection_Method;

	//------------------------------------------------------------------------- newReflectionProperty
	public static function newReflectionProperty(string $class, string $property)
		: Reflection_Property;

	//---------------------------------------------------------------------------- setAnnotationLocal
	/**
	 * Sets an annotation to local and return the local annotation object.
	 * This allows to get a copy of the notation visible into this reflection object only,
	 * that you can change without affecting others equivalent reflection objects.
	 *
	 * If the annotation was already set to local, this local annotation is returned without reset.
	 */
	public function setAnnotationLocal(string $annotation_name) : Annotation;

}
