<?php
namespace ITRocks\Framework\Reflection\Annotation\Template;

use ITRocks\Framework\Reflection\Annotation;

/**
 * A boolean annotation can have true or false value
 * Default value of these annotations are always false.
 * When the annotation is set without value, the value is true.
 * To set the value explicitly to false, annotate @annotation false or @annotation 0.
 */
class Boolean_Annotation extends Annotation
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * For boolean annotations, values are boolean and not string
	 *
	 * @override
	 * @var boolean
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Register value as boolean
	 *
	 * If a boolean annotation has no value or is not 'false' or zero, annotation's value will be true
	 *
	 * @param $value string
	 */
	public function __construct($value)
	{
		parent::__construct(
			($value !== null) && ($value !== 0) && ($value !== false) && ($value !== 'false')
		);
	}

}
