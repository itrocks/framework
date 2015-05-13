<?php
namespace SAF\Framework\Tools;

/**
 * Colors manager
 *
 * @representative value
 */
class Color implements Stringable
{

	//------------------------------------------------------------------------- Color value constants
	const BLACK   = 'black';
	const BLUE    = 'blue';
	const GREEN   = 'green';
	const MAGENTA = 'magenta';
	const RED     = 'red';
	const WHITE   = 'white';

	//---------------------------------------------------------------------------------------- $value
	/**
	 * The color value
	 *
	 * @var string
	 */
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

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @param $color string
	 */
	public function fromString($color)
	{
		$this->value = $color;
	}

	//--------------------------------------------------------------------------------- getBrightness
	/**
	 * Gets current color brightness
	 *
	 * @return float
	 */
	public function getBrightness()
	{
		$rgb = new RGB_Color($this);
		return sqrt(
			$rgb->red   * $rgb->red   * .299 +
			$rgb->green * $rgb->green * .587 +
			$rgb->blue  * $rgb->blue  * .114
		);
	}

	//---------------------------------------------------------------------------------- whiteOrBlack
	/**
	 * Return "white" if the complementary color is more white than black, "black" else.
	 *
	 * @return Color
	 */
	public function whiteOrBlack()
	{
		return new Color(($this->getBrightness() < 130) ? self::WHITE : self::BLACK);
	}

}
