<?php
namespace ITRocks\Framework\Locale;

use AllowDynamicProperties;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * A translation is the association of the origin programmed text and its translation using a given
 * language
 *
 * @representative language, text, context, translation
 * @todo Remove #AllowDynamicProperties where $id will be general to all #Store classes
 */
#[AllowDynamicProperties]
#[Display_Order('language', 'text', 'translation', 'context')]
#[Store]
class Translation
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * @var string
	 */
	public string $context = '';

	//------------------------------------------------------------------------------------- $language
	/**
	 * Allow 2 characters-length ISO codes, and those composite like nl_be, but no more
	 *
	 * @mandatory
	 * @max_length 5
	 * @var string
	 */
	public string $language = '';

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @mandatory
	 * @multiline
	 * @var string
	 */
	public string $text = '';

	//---------------------------------------------------------------------------------- $translation
	/**
	 * @multiline
	 * @var string
	 */
	public string $translation = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $text        string|null
	 * @param $language    string|null
	 * @param $context     string|null
	 * @param $translation string|null
	 */
	public function __construct(
		string $text = null, string $language = null, string $context = null, string $translation = null
	) {
		if (isset($context))     $this->context     = $context;
		if (isset($language))    $this->language    = $language;
		if (isset($text))        $this->text        = $text;
		if (isset($translation)) $this->translation = $translation;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->language
			? ('[' . $this->language . ']' . SP . $this->text . SP . ':' . SP . $this->translation)
			: '';
	}

}
