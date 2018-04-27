<?php
namespace ITRocks\Framework\Tools;

/**
 * Color stored as red, green and blue components
 */
class RGB_Color
{

	//----------------------------------------------------------------------------------------- $blue
	/**
	 * @max_value 255
	 * @min_value 0
	 * @var integer
	 */
	public $blue;

	//---------------------------------------------------------------------------------------- $green
	/**
	 * @max_value 255
	 * @min_value 0
	 * @var integer
	 */
	public $green;

	//------------------------------------------------------------------------------------------ $red
	/**
	 * @max_value 255
	 * @min_value 0
	 * @var integer
	 */
	public $red;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $red   integer|string|Color
	 * @param $green string|null
	 * @param $blue  string|null
	 */
	public function __construct($red = null, $green = null, $blue = null)
	{
		if (isset($red)) {
			if (isset($green)) {
				$this->red   = $red;
				$this->green = $green;
				$this->blue  = $blue;
			}
			else {
				$this->setHex(($red instanceof Color) ? $red->value : $red);
			}
		}
	}

	//---------------------------------------------------------------------------------------- setHex
	/**
	 * Sets a RGB Color object using an hexadecimal string color color
	 *
	 * @param $color string hexadecimal color ie '#ffffff', '2c5520', 'fff', '#ef0'
	 * @return boolean false if conversion failed (then current object stays unchanged)
	 */
	public function setHex($color)
	{
		// Gets a proper hex string
		$color   = str_replace('#', '', $color);
		$hex_str = preg_replace("/[^0-9A-Fa-f]/", '', $color);
		// If a proper hex code, convert using bitwise operation. No overhead... faster
		if (strlen($hex_str) == 6) {
			$color_val   = hexdec($hex_str);
			$this->red   = 0xFF & ($color_val >> 0x10);
			$this->green = 0xFF & ($color_val >> 0x8);
			$this->blue  = 0xFF & $color_val;
		}
		// if shorthand notation, need some string manipulations
		elseif (strlen($hex_str) == 3) {
			$this->red   = hexdec(str_repeat($hex_str[0], 2));
			$this->green = hexdec(str_repeat($hex_str[1], 2));
			$this->blue  = hexdec(str_repeat($hex_str[2], 2));
		}
		else {
			return false;
		}
		return true;
	}

}
