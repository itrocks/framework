<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Reflection\Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Options_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Types_Annotation;

/**
 * Use a specific HTML builder class to build output / edit / object for write for the property
 */
class Widget_Annotation extends Annotation
{
	use Options_Annotation;
	use Types_Annotation;

	//------------------------------------------------------------------------------------ ANNOTATION
	const ANNOTATION = 'widget';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value ?string
	 */
	public function __construct(?string $value)
	{
		$this->constructOptions($value);
		parent::__construct($value);
	}

}
