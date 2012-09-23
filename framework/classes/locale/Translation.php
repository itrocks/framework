<?php
namespace SAF\Framework;

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
	public function __construct($text = null, $language = null, $context = null, $translation = null)
	{
		$this->context     = $context;
		$this->language    = $language;
		$this->text        = $text;
		$this->translation = $translation;
	}

}
