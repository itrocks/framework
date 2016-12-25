<?php
namespace ITRocks\Framework\Reflection\Interfaces;

use ITRocks\Framework\Reflection\Annotation;

/**
 * Common interface for reflection objects
 */
interface Reflection
{

	//--------------------------------------------------------------------------------- getAnnotation
	/**
	 * Gets an single annotation of the reflected property
	 *
	 * @param $annotation_name string
	 * @return Annotation
	 */
	public function getAnnotation($annotation_name);

	//-------------------------------------------------------------------------------- getAnnotations
	/**
	 * Gets multiple annotations of the reflected property
	 *
	 * If the annotation name is given, will return the Annotation[]
	 * If no annotation name is given, all annotations will be read for the reflected property
	 *
	 * @param $annotation_name string
	 * @return Annotation[]|array
	 */
	public function getAnnotations($annotation_name = null);

	//--------------------------------------------------------------------------------------- getName
	/**
	 * Gets class name
	 *
	 * @return string
	 */
	public function getName();

	//---------------------------------------------------------------------------- setAnnotationLocal
	/**
	 * Sets an annotation to local and return the local annotation object.
	 * This enable to get a copy of the notation visible into this reflection object only,
	 * that you can change without affecting others equivalent reflection objects.
	 *
	 * If the annotation was already set to local, this local annotation is returned without reset.
	 *
	 * @param $annotation_name string
	 * @return Annotation
	 */
	public function setAnnotationLocal($annotation_name);

}
