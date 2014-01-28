<?php
namespace SAF\Framework;

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

	//---------------------------------------------------- afterReflectionPropertyValueForHtmlDisplay
	/**
	 * This patch changes HTML properties displays from a.property.display
	 * to ¦a¦.¦property¦.¦display¦ to minimize needed translations.
	 *
	 * @param $result string
	 */
	public static function afterReflectionPropertyValueDisplay(&$result)
	{
		if (strpos($result, ".") !== false) {
			$result = "¦" . str_replace(".", "¦.¦", $result) . "¦";
		}
	}

	//----------------------------------------------------------------------------------- onTranslate
	/**
	 * @param $object    Translations
	 * @param $text      string
	 * @param $context   string
	 * @param $joinpoint Around_Method_Joinpoint
	 * @return string
	 */
	public static function onTranslate(Translations $object, $text, $context, $joinpoint)
	{
		$context = isset($context) ? $context : "";
		if (strpos($text, "¦") !== false) {
			$translations = $object;
			$elements = array();
			$nelement = 0;
			$i = 0;
			while (($i = strpos($text, "¦", $i)) !== false) {
				$i += 2;
				$j = strpos($text, "¦", $i);
				if ($j >= $i) {
					$nelement ++;
					$elements["$" . $nelement] = $translations->translate(substr($text, $i, $j - $i), $context);
					$text = substr($text, 0, $i - 2) . "$" . $nelement . substr($text, $j + 2);
					$i += strlen($nelement) - 1;
				}
			}
			$i = 0;
			while (($i = strpos($text, "!", $i)) !== false) {
				$i++;
				$j = strpos($text, "!", $i);
				if (($j > $i) && (strpos(" \t\n\r", $text[$i]) === false)) {
					$nelement ++;
					$elements["$" . $nelement] = substr($text, $i, $j - $i);
					$text = substr($text, 0, $i - 1) . "$" . $nelement . substr($text, $j + 1);
				}
			}
			$translation = str_replace(
				array_keys($elements), $elements, $translations->translate($text, $context)
			);
			return $translation;
		}
		else {
			return $joinpoint->process();
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::addAroundMethodCall(
			array('SAF\Framework\Translations', "translate"),
			array(__CLASS__, "onTranslate")
		);
		Aop::addAfterMethodCall(
			array('SAF\Framework\Reflection_Property_Value', "display"),
			array(__CLASS__, "afterReflectionPropertyValueDisplay")
		);
	}

}
