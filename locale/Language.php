<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Objects\Code;

/**
 * Locale language constants
 */
class Language extends Code
{

	//-------------------------------------------------------------------------------- Language codes
	// FIXME 'be' is the Belarusian LANGUAGE code. But BE is the belgium COUNTRY code
	// The extension .be at the end of an url is a country not a language
	// Country  Codes are Uppercases https://www.w3schools.com/tags/ref_country_codes.asp
	// Language Codes are lowercases https://www.w3schools.com/tags/ref_language_codes.asp
	const BE = 'be';
	const EN = 'en';
	const ES = 'es';
	const FR = 'fr';
	const NL = 'nl';

}
