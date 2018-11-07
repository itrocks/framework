<?php
namespace ITRocks\Framework\Locale;

/**
 * A translation is the association of the origin programmed text and its translation using a given
 * language
 *
 * @business
 * @display_order language, text, translation, context
 * @representative language, text, context, translation
 */
class Translation
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * @var string
	 */
	public $context = '';

	//------------------------------------------------------------------------------------- $language
	/**
	 * Allow 2 characters-length ISO codes, and those composite like nl_be, but no more
	 *
	 * @mandatory
	 * @max_length 5
	 * @var string
	 */
	public $language = '';

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @mandatory
	 * @multiline
	 * @var string
	 */
	public $text = '';

	//---------------------------------------------------------------------------------- $translation
	/**
	 * @multiline
	 * @var string
	 */
	public $translation = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $text        string
	 * @param $language    string
	 * @param $context     string
	 * @param $translation string
	 */
	public function __construct($text = null, $language = null, $context = null, $translation = null)
	{
		if (isset($context))     $this->context     = $context;
		if (isset($language))    $this->language    = $language;
		if (isset($text))        $this->text        = $text;
		if (isset($translation)) $this->translation = $translation;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->language
			? ('[' . $this->language . ']' . SP . $this->text . SP . ':' . SP . $this->translation)
			: '';
	}

}
