<?php
namespace SAF\Framework;

@include_once "framework/vendor/geshi/geshi.php";

class GeSHi
{

	//--------------------------------------------------------------------------------------- $geshis
	/**
	 * @var \GeSHi[]
	 */
	private static $geshis;

	//----------------------------------------------------------------------------------------- parse
	/**
	 * Parse source code using language and return parsed result
	 *
	 * @param $source   string
	 * @param $language string
	 * @return string
	 */
	public static function parse($source, $language)
	{
		if (class_exists('\GeSHi')) {
			// geshi extension is installed : use it
			if (!isset(self::$geshis[$language])) {
				self::$geshis[$language] = new \GeSHi("", $language);
			}
			self::$geshis[$language]->set_source($source);
			return self::$geshis[$language]->parse_code();
		}
		else {
			// php highlighter is always here
			return "<pre>"
				. (($language == "php") ? highlight_string($source, true) : htmlentities($source))
				. "</pre>";
		}
	}

}
