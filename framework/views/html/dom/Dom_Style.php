<?php
namespace SAF\Framework;

class Dom_Style
{

	//------------------------------------------------------------------------------------------ $key
	/**
	 * @var string
	 */
	public $key;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param string $key
	 * @param string $value
	 */
	public function __construct($key = null, $value = null)
	{
		if (isset($key))   $this->key = $key;
		if (isset($value)) $this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString()
	{
		return $this->key . ": " . $this->value;
	}

}
