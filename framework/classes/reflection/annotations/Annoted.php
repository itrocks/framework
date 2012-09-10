<?php
namespace SAF\Framework;

interface Annoted
{

	//--------------------------------------------------------------------------------- getAnnotation
	/**
	 * Get Annotation named $annotation_name
	 *
	 * @param  string
	 * @return Annotation
	 */
	public function getAnnotation($annotation_name);

}
