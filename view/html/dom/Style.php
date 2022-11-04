<?php
namespace ITRocks\Framework\View\Html\Dom;

/**
 * A DOM style attribute class
 */
class Style
{

	//------------------------------------------------------------------------------------------ $key
	/**
	 * @var string
	 */
	public string $key;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var string
	 */
	public string $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $key   string|null
	 * @param $value string|null
	 */
	public function __construct(string $key = null, string $value = null)
	{
		if (isset($key))   $this->key = $key;
		if (isset($value)) $this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->key . ': ' . $this->value;
	}

}
