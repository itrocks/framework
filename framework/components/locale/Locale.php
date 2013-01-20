<?php
namespace SAF\Framework;

class Locale
{
	use Current { current as private pCurrent; }

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @getter Aop::getObject
	 * @setter setDate
	 * @var Date_Locale
	 */
	public $date;

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
		$this->setDate($parameters["date"]);
		$this->setLanguage($parameters["language"]);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Locale $set_current
	 * @return Locale
	 */
	public static function current(Locale $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//--------------------------------------------------------------------------------------- setDate
	/**
	 * @param Date_Locale | string $date if string, must be a date format (ie "d/m/Y")
	 */
	public function setDate($date)
	{
		if ($date instanceof Date_Locale) {
			$this->date = $date;
		}
		else {
			$this->date = new Date_Locale($date);
		}
	}

	//----------------------------------------------------------------------------------- setLanguage
	/**
	 * @param string $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
		$this->translations = new Translations($this->language);
	}

}
