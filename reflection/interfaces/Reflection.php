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

}
