<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Traits\Has_Code_And_Name;

/**
 * Locale language constants
 */
class Language
{
	use Has_Code_And_Name;

	//-------------------------------------------------------------------------------- Language codes
	// FIXME 'be' is the Belarusian LANGUAGE code. But BE is the belgium COUNTRY code
	// The extension .be at the end of an url is a country not a language
	// Country  Codes are uppercases https://www.w3schools.com/tags/ref_country_codes.asp
	// Language Codes are lowercases https://www.w3schools.com/tags/ref_language_codes.asp
	// Please respect ISO 639-1 : https://fr.wikipedia.org/wiki/Liste_des_codes_ISO_639-1
	const BE = 'be';
	const EN = 'en';
	const ES = 'es';
	const FR = 'fr';
	const NL = 'nl';

	//----------------------------------------------------------------------------------------- FLAGS
	const FLAGS = [
		self::EN => 'gb'
	];

	//------------------------------------------------------------------------------------------ flag
	/**
	 * Convert language code to components/flag-icon-css flag code
	 *
	 * @return string
	 */
	public function flag() : string
	{
		return static::FLAGS[$this->code] ?? $this->code;
	}

}
