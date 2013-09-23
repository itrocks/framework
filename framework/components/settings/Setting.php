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

}
