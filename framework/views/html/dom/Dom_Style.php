<?php
namespace SAF\Framework;

/**
 * A DOM style attribute class
 */
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
	 * @param $key string
	 * @param $value string
	 */
	public function __construct($key = null, $value = null)
	{
		if (isset($key))   $this->key = $key;
		if (isset($value)) $this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->key . ": " . $this->value;
	}

}
