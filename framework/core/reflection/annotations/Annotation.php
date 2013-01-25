<?php
namespace SAF\Framework;

class Annotation
{

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Annotation value
	 *
	 * @var string
	 */
	public $value;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * Default annotation constructor receive the full doc text content
	 *
	 * Annotation class will have to parse it ie for several parameters or specific syntax, or if they want to store specific typed or calculated value
	 *
	 * @param $value string
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString()
	{
		return strval($this->value);
	}

}
