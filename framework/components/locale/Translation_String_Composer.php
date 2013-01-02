<?php
namespace SAF\Framework;
use AopJoinpoint;

/**
 * Compose translations with dynamic elements with separated translations
 *
 * Enables to prefer multiple translations of single words instead of big sentences translations
 * Enables translations to sort words in another order than original language
 *
 * @example
 * "A text" is a simple translation string, directly translated without particular work
 * "¦Sales orders¦ list" will be dynamically translated : first "Sales orders", then "$1 list"
 * "¦Sales¦ ¦orders¦ list" will translate "Sales" then "orders" then "$1 $2 list"
 */
abstract class Translation_String_Composer implements Plugin
{

	//----------------------------------------------------------------------------------- onTranslate
	/**
	 * @param AopJoinpoint $joinpoint
	 * @return string
	 */
	public static function onTranslate(AopJoinpoint $joinpoint)
	{
		$args = $joinpoint->getArguments();
		$text = $args[0];
		$context = isset($args[1]) ? $args[1] : "";
		if (strpos($text, "¦") !== false) {
			$translations = $joinpoint->getObject();
			$elements = array();
			$nelement = 0;
			$i = 0;
			while (($i = strpos($text, "¦", $i)) !== false) {
				$i += 2;
				$j = strpos($text, "¦", $i);
				if ($j >= $i) {
					$nelement ++;
					$elements["$" . $nelement] = $translations->translate(
						substr($text, $i, $j - $i), $context
					);
					$text = substr($text, 0, $i - 2) . "$" . $nelement . substr($text, $j + 2);
					$i += strlen($nelement) - 1;
				}
			}
			$translation = str_replace(
				array_keys($elements), $elements, $translations->translate($text, $context)
			);
			$joinpoint->setReturnedValue($translation);
		}
		else {
			$joinpoint->process();
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("around",
			__NAMESPACE__ . "\\Translations->translate()",
			array(__CLASS__, "onTranslate")
		);
	}

}
