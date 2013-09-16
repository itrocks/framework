<?php
namespace SAF\Framework;

/**
 * An annoted class contains annotations.
 *
 * Common annoted classes are Reflection_Class, Reflection_Property, Reflection_Method.
 * Classes that use this trait must implement Has_Doc_Comment !
 */
trait Annoted
{

	//---------------------------------------------------------------------------------- $annotations
	/**
	 * Annotations values
	 *
	 * @var Annotation[]
	 */
	private $annotations = array();

	//--------------------------------------------------------------------------------- getAnnotation
	/**
	 * Gets an single annotation of the reflected property
	 *
	 * @param $annotation_name string
	 * @return Annotation
	 */
	public function getAnnotation($annotation_name)
	{
		return $this->getCachedAnnotation($annotation_name, false);
	}

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
	public function getAnnotations($annotation_name = null)
	{
		if (isset($annotation_name)) {
			return $this->getCachedAnnotation($annotation_name, true);
		}
		else {
			/** @var $this Annoted|Has_Doc_Comment */
			return Annotation_Parser::allAnnotations($this);
		}
	}

	//--------------------------------------------------------------------------- getCachedAnnotation
	/**
	 * @param $annotation_name string
	 * @param $multiple        boolean
	 * @return Annotation|Annotation[] depending on $multiple value
	 */
	private function getCachedAnnotation($annotation_name, $multiple)
	{
		if (
			!isset($this->annotations[$annotation_name][$multiple])
			&& ($this instanceof Has_Doc_Comment)
		) {
			/** @var $this Annoted|Has_Doc_Comment (PhpStorm Inspector patch) */
			$this->annotations[$annotation_name][$multiple] = Annotation_Parser::byName(
				$this, $annotation_name, $multiple
			);
		}
		return $this->annotations[$annotation_name][$multiple];
	}

	//----------------------------------------------------------------------------- getListAnnotation
	/**
	 * Gets an List_Annotation for the reflected property
	 *
	 * @param $annotation_name string
	 * @return List_Annotation
	 */
	public function getListAnnotation($annotation_name)
	{
		$annotation = $this->getCachedAnnotation($annotation_name, false);
		if (!($annotation instanceof List_Annotation)) {
			trigger_error("Bad annotation type getListAnnotation('$annotation_name')", E_USER_ERROR);
		}
		return $annotation;
	}

	//---------------------------------------------------------------------------- getListAnnotations
	/**
	 * Gets multiple List_Annotation for the reflected property
	 *
	 * @param $annotation_name string
	 * @return List_Annotation[]
	 */
	public function getListAnnotations($annotation_name)
	{
		$annotations = $this->getCachedAnnotation($annotation_name, true);
		if ($annotations && !(reset($annotations) instanceof List_Annotation)) {
			trigger_error("Bad annotation type getListAnnotations('$annotation_name')", E_USER_ERROR);
		}
		return $annotations;
	}

	//--------------------------------------------------------------------------------- setAnnotation
	/**
	 * Sets an annotation value for the reflected property (use it when no annotation found)
	 *
	 * @param $annotation_name string
	 * @param $annotation      Annotation
	 */
	protected function setAnnotation($annotation_name, Annotation $annotation)
	{
		$this->annotations[$annotation_name][false] = $annotation;
	}

	//-------------------------------------------------------------------------------- setAnnotations
	/**
	 * Sets a multiple annotations value for the reflected property (use it when no annotation found)
	 *
	 * @param $annotation_name string
	 * @param $annotations     Annotation[]
	 */
	protected function setAnnotations($annotation_name, $annotations)
	{
		$this->annotations[$annotation_name][true] = $annotations;
	}

}
