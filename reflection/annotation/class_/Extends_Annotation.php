<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation\Template;
use ITRocks\Framework\Reflection\Annotation\Template\Do_Not_Inherit;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * This must be used for traits that are designed to extend a given class
 * Builder will use it to sort built classes
 */
class Extends_Annotation extends Template\List_Annotation implements Do_Not_Inherit
{
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'extends';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 */
	public function __construct($value)
	{
		// TODO remove this totally if it is confirmed it was not the right thing to do
		//$this->build_class_name = false;
		parent::__construct($value);
	}

}
