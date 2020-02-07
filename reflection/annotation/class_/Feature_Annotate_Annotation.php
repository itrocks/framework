<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * The feature adds an annotation to a class when installed
 */
class Feature_Annotate_Annotation extends Template\List_Annotation
{
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_annotate';

	//----------------------------------------------------------------------------------- $annotation
	/**
	 * @var string
	 */
	public $annotation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 */
	public function __construct($value)
	{
		list($value, $annotation) = explode(SP, $value, 2);
		$this->annotation = trim($annotation);
		parent::__construct($value);
	}

}
