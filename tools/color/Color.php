<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Tools\Color\RGB;

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
	public string $value = 'white';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $value string|null
	 */
	public function __construct(string $value = null)
	{
		if (isset($value)) {
			$this->value = $value;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->value;
	}

	//------------------------------------------------------------------------------------ fromString
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $string string
	 * @return static
	 */
	public static function fromString(string $string) : static
	{
		/** @noinspection PhpUnhandledExceptionInspection class */
		return Builder::create(static::class, [$string]);
	}

	//--------------------------------------------------------------------------------- getBrightness
	/**
	 * Gets current color brightness
	 *
	 * @return float
	 */
	public function getBrightness() : float
	{
		$rgb = new RGB($this);
		return sqrt(
			($rgb->red * $rgb->red * .299)
			+ ($rgb->green * $rgb->green * .587)
			+ ($rgb->blue * $rgb->blue * .114)
		);
	}

	//---------------------------------------------------------------------------------- whiteOrBlack
	/**
	 * Return "white" if the complementary color is more white than black, "black" else.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return static
	 */
	public function whiteOrBlack() : static
	{
		/** @noinspection PhpUnhandledExceptionInspection class */
		return Builder::create(
			static::class, [($this->getBrightness() < 130) ? self::WHITE : self::BLACK]
		);
	}

}
