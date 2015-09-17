<?php
namespace SAF\Framework;

/**
 * An application setting
 */
class Setting
{

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var string
	 */
	public $code;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @getter getValue
	 * @max_length 1000000000
	 * @var string|object
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $code  string
	 * @param $value string|object
	 */
	public function __construct($code = null, $value = null)
	{
		if (isset($code))  $this->code = $code;
		if (isset($value)) $this->value = $value;
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return string
	 */
	public function getClass()
	{
		return explode(DOT, $this->code)[0];
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * @return string
	 */
	public function getFeature()
	{
		return explode(DOT, $this->code)[1];
	}

	//-------------------------------------------------------------------------------------- getValue
	/** @noinspection PhpUnusedPrivateMethodInspection @getter */
	/**
	 * @return string|object
	 */
	private function getValue()
	{
		$value = $this->value;
		return (
			isset($value)
			&& is_string($value)
			&& (substr($value, 0, 2) == 'O:')
			&& substr($value, -1) === '}'
		) ? ($this->value = unserialize($value))
			: $value;
	}

}
