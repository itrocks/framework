<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Class_Context_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * The installation of the features will install this menu entry
 */
class Feature_Menu_Annotation extends Annotation implements Class_Context_Annotation
{
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'feature_menu';

	//---------------------------------------------------------------------------------------- $block
	/**
	 * @var string
	 */
	public $block;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 * @param $class Reflection_Class
	 */
	public function __construct($value, Reflection_Class $class)
	{
		$this->block = lParse($value, SP);
		parent::__construct(trim(rParse($value, SP)) ?: $class->getName());
	}

}
