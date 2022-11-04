<?php
namespace ITRocks\Framework\Locale;

/**
 * Option for locale functions
 *
 * @see Loc::tr
 */
abstract class Option
{

	//------------------------------------------------------------------------------ afterTranslation
	/**
	 * Action on text after translation
	 *
	 * @param $translation string
	 * @return string
	 */
	public function afterTranslation(string $translation) : string
	{
		return $translation;
	}

}
