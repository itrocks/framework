<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Builder;

/**
 * Colors manager
 *
 * @representative value
 */
class Color implements Stringable
{

	//------------------------------------------------------------------------- Color value constants
	//----------------------------------------------------------------------------------------- BLACK
	const BLACK = 'black';

	//------------------------------------------------------------------------------------------ BLUE
	const BLUE = 'blue';

	//----------------------------------------------------------------------------------------- GREEN
	const GREEN = 'green';

	//--------------------------------------------------------------------------------------- MAGENTA
	const MAGENTA = 'magenta';

	//------------------------------------------------------------------------------------------- RED
	const RED = 'red';

	//----------------------------------------------------------------------------------------- WHITE
	const WHITE = 'white';

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
	 * @return static
	 */
	public static function fromString($color)
	{
		/** @var $color static */
		$color = Builder::create(get_called_class(), [$color]);
		return $color;
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
