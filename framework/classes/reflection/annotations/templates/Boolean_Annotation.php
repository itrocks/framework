<?php
namespace SAF\Framework;

abstract class Boolean_Annotation extends Annotation
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * For boolean annotations, values are boolean and not string
	 *
	 * @override
	 * @var boolean
	 */
	public $value;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Register value as boolean
	 *
	 * If a boolean annotation has no value or is not "false" or zero, annotation's value will be true. 
	 *
	 * @param string $value
	 */
	public function __construct($value)
	{
		$this->value = (
			($value !== null) && ($value !== 0) && ($value !== false) && ($value !== "false")
		);
	}

}
