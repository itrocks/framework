<?php
namespace SAF\Framework\Locale\Option;

use SAF\Framework\Locale\Loc;
use SAF\Framework\Locale\Option;
use SAF\Framework\Tools\Date_Time;

/**
 * Option to replace some $values into $text using a replacement table.
 * Each key into $replaces will be prefixed with an $ and replaced in $text by their value.
 * Date-time values are formatted using locale.
 * If you need to translate replacement values, you must call Loc::tr() for each of them.
 *
 * @example tr('Error for $number elements', new Replace(['number' => 12]))
 *          => 'Error for 12 elements'
 */
class Replace extends Option
{

	//-------------------------------------------------------------------------------------- $replace
	/**
	 * List of keys to be replaces by values
	 * If it's datetime, convert to local format
	 * If it's object, transform object to string
	 * @var string[]|object[] [key => value]
	 */
	public $replace = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Replace constructor.
	 *
	 * @param $replace string[]|object[] List of keys to be replaces by values
	 */
	public function __construct(array $replace = [])
	{
		$this->replace = $replace;
	}

	//------------------------------------------------------------------------------ afterTranslation
	/**
	 * Replace keys in translation by their values
	 *
	 * @param $translation string
	 * @return string
	 */
	public function afterTranslation($translation)
	{
		foreach ($this->replace as $key => $value) {
			if (is_object($value)) {
				$value = ($value instanceof Date_Time) ? Loc::dateToLocale($value) : strval($value);
			}
			$translation = str_replace('$' . $key, $value, $translation);
		}
		return parent::afterTranslation($translation);
	}

}
