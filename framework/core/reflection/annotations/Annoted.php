<?php
namespace SAF\Framework;

/**
 * Classes that use this trait must implement Has_Doc_Comment !
 */
trait Annoted
{

	//---------------------------------------------------------------------------------- $annotations
	/**
	 * Annotations values
	 *
	 * @var array
	 */
	private $annotations = array();

	//--------------------------------------------------------------------------------- getAnnotation
	/**
	 * Gets an annotation of the reflected property
	 *
	 * @param string
	 * @return Annotation
	 */
	public function getAnnotation($annotation_name)
	{
		if (!isset($this->annotations[$annotation_name])) {
			$this->annotations[$annotation_name] = Annotation_Parser::byName($this, $annotation_name);
		}
		return $this->annotations[$annotation_name];
	}

	//--------------------------------------------------------------------------------- setAnnotation
	/**
	 * Sets an annotation value for the reflected property (use it when no annotation found)
	 *
	 * @param string $annotation_name
	 * @param Annotation $annotation
	 */
	protected function setAnnotation($annotation_name, Annotation $annotation)
	{
		$this->annotations[$annotation_name] = $annotation;
	}

}
