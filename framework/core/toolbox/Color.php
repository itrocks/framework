<?php
namespace SAF\Framework;

/**
 * Colors manager
 */
class Color
{

	public $value = 'white';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string
	 */
	public function __construct($value = null)
	{
		if (isset($value)) {
			$this->value = $value;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}

	//-------------------------------------------------------------------------------------------- of
	/**
	 * @param $value string
	 * @return Color
	 */
	public static function of($value)
	{
		return new Color($value);
	}

}
