<?php
namespace ITRocks\Framework\Reflection\Annotation;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\List_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Multiple_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Has_Doc_Comment;

/**
 * An annoted class contains annotations.
 *
 * Common annoted classes are Reflection_Class, Reflection_Property, Reflection_Method.
 * Classes that use this trait must implement Has_Doc_Comment !
 *
 * @implements Has_Doc_Comment
 */
trait Annoted
{

	//---------------------------------------------------------------------------------- $annotations
	/**
	 * Local annotations cache
	 *
	 * Key is the name of the annotation.
	 * Value is :
	 * - an Annotation, if the annotation is single
	 * - an array of annotations Annotation[], if the annotation is multiple
	 *
	 * @var array
	 */
	private $annotations = [];

	//---------------------------------------------------------------------------- $annotations_cache
	/**
	 * Global annotations cache
	 *
	 * Annotation['Class_Name'][AT]['annotation'][$is_multiple]
	 * Annotation['Class_Name']['property']['annotation'][$is_multiple]
	 * Annotation['Class_Name']['methodName()']['annotation'][$is_multiple]
	 *
	 * @var array
	 */
	private static $annotations_cache = [];

	//--------------------------------------------------------------------------------- addAnnotation
	/**
	 * Add an annotation, to a multiple annotations
	 *
	 * Don't call this with non-multiples annotations or it will crash your application !
	 *
	 * @param $annotation_name string
	 * @param $annotation      Annotation
	 */
	public function addAnnotation($annotation_name, Annotation $annotation)
	{
		$path = $this->getAnnotationCachePath();
		$this->getAnnotations($annotation_name);
		self::$annotations_cache[$path[0]][$path[1]][$annotation_name][true][] = $annotation;
	}

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

	//------------------------------------------------------------------------ getAnnotationCachePath
	/**
	 * @return string[]
	 */
	protected function getAnnotationCachePath()
	{
		return null;
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
			// save cached annotations
			$cached_annotations = $this->getCachedAnnotations();
			// parse phpdoc annotations
			$annotations = Parser::allAnnotations($this);
			// merge cached annotations in parsed annotations
			foreach ($cached_annotations as $annotation_name => $cached_annotation) {
				$annotation = $cached_annotation[0];
				if (!isset($annotations[$annotation_name])) {
					$annotations[$annotation_name] = $annotation;
				}
			}
			return $annotations;
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
		if (isset($this->annotations[$annotation_name])) {
			return $this->annotations[$annotation_name];
		}
		$path = $this->getAnnotationCachePath();
		if (isset($path)) {
			if (
				!isset(self::$annotations_cache[$path[0]][$path[1]][$annotation_name][$multiple])
				&& ($this instanceof Has_Doc_Comment)
			) {
				/** @var $this Annoted|Has_Doc_Comment */
				self::$annotations_cache[$path[0]][$path[1]][$annotation_name][$multiple]
					= Parser::byName($this, $annotation_name, $multiple);
			}
			return self::$annotations_cache[$path[0]][$path[1]][$annotation_name][$multiple];
		}
		else {
			/** @var $this Annoted|Has_Doc_Comment */
			return Parser::byName($this, $annotation_name, $multiple);
		}
	}

	//-------------------------------------------------------------------------- getCachedAnnotations
	/**
	 * @return array [$annotation_name => [$annotation, $multiple, $annotation_name]]
	 */
	public function getCachedAnnotations()
	{
		$cached_annotations = [];
		$path = $this->getAnnotationCachePath();
		if (isset(self::$annotations_cache[$path[0]][$path[1]])) {
			foreach (self::$annotations_cache[$path[0]][$path[1]] as $annotation_name => $annotations) {
				foreach ($annotations as $multiple => $annotation) {
					$cached_annotations[$annotation_name] = [$annotation, $multiple, $annotation_name];
				}
			}
		}
		return $cached_annotations;
	}

	//----------------------------------------------------------------------------- getListAnnotation
	/**
	 * Gets a List_Annotation for the reflected property
	 *
	 * @param $annotation_name string
	 * @return List_Annotation
	 */
	public function getListAnnotation($annotation_name)
	{
		$annotation = $this->getCachedAnnotation($annotation_name, false);
		if (!($annotation instanceof List_Annotation)) {
			trigger_error(
				'Bad annotation type getListAnnotation(' . $annotation_name . ')', E_USER_ERROR
			);
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
			trigger_error(
				'Bad annotation type getListAnnotations(' . $annotation_name . ')', E_USER_ERROR
			);
		}
		/** @var $annotations List_Annotation[] */
		return $annotations;
	}

	//---------------------------------------------------------------------------- isAnnotationCached
	/**
	 * Return true if the annotation is set in cache
	 *
	 * @param $annotation_name string
	 * @param $multiple boolean
	 * @return boolean
	 */
	public function isAnnotationCached($annotation_name, $multiple)
	{
		$path = $this->getAnnotationCachePath();
		return isset($path)
			&& isset(self::$annotations_cache[$path[0]][$path[1]][$annotation_name][$multiple]);
	}

	//------------------------------------------------------------------------------ removeAnnotation
	/**
	 * Remove an annotation, identified by its class and value, from a multiple annotations
	 *
	 * Don't call this with non-multiples annotations or it will crash your application !
	 *
	 * @param $annotation_name string
	 * @param $annotation      Annotation|null if null : annotation / all annotations from list
	 */
	public function removeAnnotation($annotation_name, Annotation $annotation = null)
	{
		$path = $this->getAnnotationCachePath();
		if (!$annotation) {
			self::$annotations_cache[$path[0]][$path[1]][$annotation_name][true] = [];
			return;
		}
		$this->getAnnotations($annotation_name);
		foreach (
			self::$annotations_cache[$path[0]][$path[1]][$annotation_name][true]
			as $key => $old_annotation
		) {
			/** @var $old_annotation Annotation */
			if (
				!$annotation
				|| (
					(get_class($annotation) === get_class($old_annotation))
					&& ($old_annotation->value === $annotation->value)
				)
			) {
				unset(self::$annotations_cache[$path[0]][$path[1]][$annotation_name][true][$key]);
			}
		}
	}

	//--------------------------------------------------------------------------------- setAnnotation
	/**
	 * Sets an annotation value for the reflected object (use it when no annotation found)
	 *
	 * The annotation value will be set for all equivalent reflection objects.
	 * If you want to change the annotation for a local reflection object only, please consider
	 * using setAnnotationLocal($annotation_name) and modifying the local annotation instead.
	 *
	 * Default value for $annotation_name will be $annotation::ANNOTATION.
	 *
	 * @param $annotation_name string|Annotation optional forced name for the annotation
	 * @param $annotation      Annotation the forced value for the annotation
	 */
	public function setAnnotation($annotation_name, Annotation $annotation = null)
	{
		if ($annotation_name instanceof Annotation) {
			$annotation      = $annotation_name;
			$annotation_name = $annotation::ANNOTATION;
		}
		$path = $this->getAnnotationCachePath();
		self::$annotations_cache[$path[0]][$path[1]][$annotation_name][false] = $annotation;
	}

	//---------------------------------------------------------------------------- setAnnotationLocal
	/**
	 * Sets an annotation to local and return the local annotation object.
	 * This enable to get a copy of the annotation visible into this reflection object only,
	 * that you can change without affecting others equivalent reflection objects.
	 *
	 * If the annotation was already set to local, this local annotation is returned without reset.
	 *
	 * @param $annotation_name string
	 * @return Annotation
	 */
	public function setAnnotationLocal($annotation_name)
	{
		return isset($this->annotations[$annotation_name])
			? $this->annotations[$annotation_name]
			: ($this->annotations[$annotation_name] = clone $this->getAnnotation($annotation_name));
	}

	//-------------------------------------------------------------------------------- setAnnotations
	/**
	 * Sets a multiple annotations value for the reflected object (use it when no annotation found)
	 *
	 * The annotation values will be set for all equivalent reflection objects.
	 * If you want to change the annotations for a local reflection object only, please consider
	 * using setAnnotationLocal($annotation_name) and modifying the local annotations instead.
	 *
	 * @param $annotation_name string
	 * @param $annotations     Annotation[]
	 */
	public function setAnnotations($annotation_name, array $annotations)
	{
		$path = $this->getAnnotationCachePath();
		self::$annotations_cache[$path[0]][$path[1]][$annotation_name][true] = $annotations;
	}

	//--------------------------------------------------------------------------- setAnnotationsLocal
	/**
	 * Sets a multiple annotations to local and return the local annotations objects.
	 * This enable to get a copy of the annotations visible into this reflection object only,
	 * that you can change without affecting others equivalent reflection objects.
	 *
	 * If the annotations were already set to local, these local annotations are returned without
	 * reset.
	 *
	 * @param $annotation_name string
	 * @param $annotations     Multiple_Annotation[] Optional : force the new annotations
	 * @return Annotation[]
	 */
	public function & setAnnotationsLocal($annotation_name, array $annotations = null)
	{
		if (isset($annotations)) {
			$this->annotations[$annotation_name] = $annotations;
			return $annotations;
		}
		if (isset($this->annotations[$annotation_name])) {
			return $this->annotations[$annotation_name];
		}
		$annotations = $this->getAnnotations($annotation_name);
		foreach ($annotations as $key => $annotation) {
			$annotations[$key] = clone $annotation;
		}
		$this->annotations[$annotation_name] =& $annotations;
		return $annotations;
	}

}
