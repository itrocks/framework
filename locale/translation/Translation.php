<?php
namespace ITRocks\Framework\Locale;

use AllowDynamicProperties;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\Multiline;

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
	public string $context = '';

	//------------------------------------------------------------------------------------- $language
	/**
	 * Allow 2 characters-length ISO codes, and those composite like nl_be, but no more
	 *
	 * @max_length 5
	 */
	#[Mandatory]
	public string $language = '';

	//----------------------------------------------------------------------------------------- $text
	#[Mandatory, Multiline]
	public string $text = '';

	//---------------------------------------------------------------------------------- $translation
	#[Multiline]
	public string $translation = '';

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		string $text = null, string $language = null, string $context = null, string $translation = null
	) {
		if (isset($context))     $this->context     = $context;
		if (isset($language))    $this->language    = $language;
		if (isset($text))        $this->text        = $text;
		if (isset($translation)) $this->translation = $translation;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->language
			? ('[' . $this->language . ']' . SP . $this->text . SP . ':' . SP . $this->translation)
			: '';
	}

}
