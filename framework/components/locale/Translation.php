<?php
namespace SAF\Framework;

/**
 * A translation is the association of the origin programmed text and it's translation using a given language
 */
class Translation
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * @var string
	 */
	public $context;

	//------------------------------------------------------------------------------------- $language
	/**
	 * @var string
	 */
	public $language;

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @var string
	 */
	public $text;

	//---------------------------------------------------------------------------------- $translation
	/**
	 * @var string
	 */
	public $translation;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $text        string
	 * @param $language    string
	 * @param $context     string
	 * @param $translation string
	 */
	public function __construct($text = null, $language = null, $context = null, $translation = null)
	{
		if (isset($text)) {
			$this->context     = $context;
			$this->language    = $language;
			$this->text        = $text;
			$this->translation = $translation;
		}
	}

}
