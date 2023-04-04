<?php
namespace ITRocks\Framework\Tools\Color;

use ITRocks\Framework\Feature\Validate\Property\Max_Value;
use ITRocks\Framework\Feature\Validate\Property\Min_Value;
use ITRocks\Framework\Tools\Color;

/**
 * Color stored as red, green and blue components
 */
class RGB
{

	//----------------------------------------------------------------------------------------- $blue
	#[Max_Value(255), Min_Value(0)]
	public int $blue;

	//---------------------------------------------------------------------------------------- $green
	#[Max_Value(255), Min_Value(0)]
	public int $green;

	//------------------------------------------------------------------------------------------ $red
	#[Max_Value(255), Min_Value(0)]
	public int $red;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		Color|int|string $red = null, string $green = null, string $blue = null
	) {
		if (!isset($red)) {
			return;
		}
		if (isset($green)) {
			$this->red   = $red;
			$this->green = $green;
			$this->blue  = $blue;
		}
		else {
			$this->setHex(($red instanceof Color) ? $red->value : $red);
		}
	}

	//---------------------------------------------------------------------------------------- setHex
	/**
	 * Sets an RGB Color object using a hexadecimal string color
	 *
	 * @param $color string hexadecimal color ie '#ffffff', '2c5520', 'fff', '#ef0'
	 * @return boolean false if conversion failed (then current object stays unchanged)
	 */
	public function setHex(string $color) : bool
	{
		// Gets a proper hex string
		$color   = str_replace('#', '', $color);
		$hex_str = preg_replace("/[^0-9A-Fa-f]/", '', $color);
		// If a proper hex code, convert using bitwise operation. No overhead... faster
		if (strlen($hex_str) === 6) {
			$color_val   = hexdec($hex_str);
			$this->red   = 0xFF & ($color_val >> 0x10);
			$this->green = 0xFF & ($color_val >> 0x8);
			$this->blue  = 0xFF & $color_val;
		}
		// if shorthand notation, need some string manipulations
		elseif (strlen($hex_str) === 3) {
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
