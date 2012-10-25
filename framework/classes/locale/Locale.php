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

	//----------------------------------------------------------------------------------- __construct
	public function __construct($parameters)
	{
		$this->language = $parameters["language"];
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Locale $set_current
	 * @return Locale
	 */
	public static function current(Locale $set_current = null)
	{
		return parent::current($set_current);
	}

	//----------------------------------------------------------------------------------- setLanguage
	public function setLanguage($language)
	{
		$this->language = $language;
		$this->translations = new Translations($this->language);
	}

}
