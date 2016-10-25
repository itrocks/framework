<?php
namespace SAF\Framework\Locale;

/**
 * Option for locate functions
 * For the moment : only Loc::tr
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
	public function afterTranslation($translation)
	{
		return $translation;
	}

}
