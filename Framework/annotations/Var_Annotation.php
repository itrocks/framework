<?php

class Var_Annotation extends Annotation
{

	/**
	 * @var string
	 */
	public $documentation;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($value)
	{
		$values = split(" ", $value);
		parent::__construct($values[0]);
		$this->documentation = trim(substr($value, strlen($values[0])));
	}

}
