<?php

class Mandatory_Annotation extends Annotation
{

	/**
	 * @var boolean
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($value)
	{
		parent::__construct($value);
		$value = (($value !== null) && ($value !== 0) && ($value !== false) && ($value !== "false"));
	}

}
