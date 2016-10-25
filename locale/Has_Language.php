<?php

namespace SAF\Framework\Locale;

/**
 * Add language field
 * Objects with this trait can be used in Loc::tr as option to use object language for translation
 */
trait Has_Language
{

	//------------------------------------------------------------------------------------- $language
	/**
	 * @link Object
	 * @var Language
	 */
	public $language;
}
