<?php
namespace SAF\Framework;

class Locale
{
	use Current;

	//------------------------------------------------------------------------------------- $language
	/**
	 * @setter setLanguage
	 * @var string
	 */
	public $language;

	//--------------------------------------------------------------------------------- $translations
	/**
	 * @var Translations
	 */
	public $translations;

	//----------------------------------------------------------------------------------- setLanguage
	public function setLanguage()
	{
		$this->translations = new Translations($this->language);
	}

}
