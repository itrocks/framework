<?php
namespace ITRocks\Framework\Locale;

/**
 * Adds $language property
 *
 * Objects with this trait can be used in Loc::tr as option to use object language for translation
 */
trait Has_Language
{

	//------------------------------------------------------------------------------------- $language
	/**
	 * @link Object
	 * @var ?Language
	 */
	public ?Language $language;

}
