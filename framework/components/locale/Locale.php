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

	//--------------------------------------------------------------------------------------- $number
	/**
	 * @setter setNumber
	 * @var Number_Format
	 */
	public $number;

	//--------------------------------------------------------------------------------- $translations
	/**
	 * @var Translations
	 */
	public $translations;

	//----------------------------------------------------------------------------------- __construct
	public function __construct($parameters = null)
	{
		if (isset($parameters)) {
			$this->setDate($parameters["date"]);
			$this->setLanguage($parameters["language"]);
			$this->setNumber($parameters["number"]);
		}
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
		$this->date = ($date instanceof Date_Locale)
			? $date
			: new Date_Locale($date);
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

	//------------------------------------------------------------------------------------- setNumber
	/**
	 * Set locale's number
	 *
	 * @param Number_Locale | multitype:mixed
	 */
	public function setNumber($number)
	{
		$this->number = ($number instanceof Number_Locale)
			? $number
			: new Number_Locale($number);
	}

}
