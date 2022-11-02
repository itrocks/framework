<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Annotation;

/**
 * A boolean annotation can have true or false value
 * Default value of these annotations are always false.
 * When the annotation is set without value, the value is true.
 * To set the value explicitly to false, annotate @annotation false or @annotation 0.
 *
 * @override value @var boolean
 * @property boolean value
 */
class Boolean_Annotation extends Annotation
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Register value as boolean
	 *
	 * If a boolean annotation has no value or is not 'false' or zero, annotation's value will be true
	 *
	 * @param $value bool|null|string
	 */
	public function __construct(bool|null|string $value)
	{
		parent::__construct($value);
		$this->value = ($value !== _FALSE) && !empty($value);
	}

}
