<?php
namespace ITRocks\Framework\Feature\Export\PDF;

/**
 * PDF standard fonts
 */
abstract class Font
{

	//------------------------------------------------------------------------- font styles constants
	const BOLD   = 'B';
	const ITALIC = 'I';
	const NORMAL = '';

	//----------------------------------------------------------------------- font families constants
	const COURIER   = 'courier';
	const DINGBATS  = 'zapfdingbats';
	const HELVETICA = 'helvetica';
	const SYMBOL    = 'symbol';
	const TIMES     = 'times';

	//------------------------------------------------------------------------------------------- get
	/**
	 * Get font string knowing its font constant name and style
	 *
	 * @param $font  string Any Font::* font family constant
	 *               COURIER, DINGBATS, HELVETICA, SYMBOL, TIMES
	 * @param $style string|string[] Font::NORMAL or (Font::BOLD and/or Font::ITALIC)
	 * @return string
	 */
	public static function get($font, $style = self::NORMAL)
	{
		if (is_array($style)) {
			sort($style);
			$style = join('', $style);
		}
		return $font . ((!in_array($font, [self::DINGBATS, self::SYMBOL])) ? $style : '');
	}

}
