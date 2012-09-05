<?php
namespace SAF\Framework;

class Boolean_Annotation extends Annotation
{

	/**
	 * @override
	 * @var string
	 */
	public $value;

	//---------------------------------------------------------------------------------------- $value
	public function __construct($value)
	{
		parent::__construct($value);
		$value = (($value !== null) && ($value !== 0) && ($value !== false) && ($value !== "false"));
	}

}
