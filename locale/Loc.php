<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Feature\Import\Import_Array;
use ITRocks\Framework\Feature\List_Setting;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Option\Context;
use ITRocks\Framework\Locale\Option\Replace;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Method;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View\Html\Template\Functions;
use ITRocks\Framework\View\User_Error_Exception;
use ReflectionException;
use Reflector;

/**
 * Locale plugin concentrates locale translation / formatting features into simple static calls
 */
class Loc implements Registerable
{

	//-------------------------------------------------------------------------------------- FEMININE
	const FEMININE = 'f';

	//------------------------------------------------------------------------------------- MASCULINE
	const MASCULINE = 'm';

	//--------------------------------------------------------------------------------------- NEUTRAL
	const NEUTRAL = 'n';

	//------------------------------------------------------------------------------- $contexts_stack
	/**
	 * Current context stack for translations
	 *
	 * This contains class names only
	 * The last pushed class name is the current context
	 * The others class names are here for recursion
	 *
	 * Typical Classes that are treated well for now :
	 * - business classes are returned by getContext() as being the current context
	 * - reflection classes (that implement Reflector) are stacked but ignored by getContext()
	 *
	 * @var string[]
	 */
	public static array $contexts_stack = [];

	//-------------------------------------------------------------------------------------- $enabled
	/**
	 * If true, translation features are disabled
	 *
	 * @see enable()
	 * @var boolean
	 */
	public static bool $enabled = true;

	//------------------------------------------------- afterHtmlTemplateFunctionsToEditPropertyExtra
	/**
	 * @param $result array[]
	 * @return array[]
	 */
	public function afterHtmlTemplateFunctionsToEditPropertyExtra(array $result)
	{
		/** @var $property      Reflection_Property */
		/** @var $property_path string */
		/** @var $value         mixed */
		list($property, $property_path, $value) = $result;
		$value = self::propertyToLocale($property, $value);
		return [$property, $property_path, $value];
	}

	//------------------------------------------------------- beforeObjectBuilderArrayBuildBasicValue
	/**
	 * @param $property Reflection_Property
	 * @param $value    boolean|integer|float|string|array
	 * @throws ReflectionException
	 */
	public function beforeObjectBuilderArrayBuildBasicValue(
		Reflection_Property $property, &$value
	) {
		if (isset($value)) {
			if (is_array($value) && !empty($value)) {
				if (Link_Annotation::of($property)->isCollection()) {
					$class      = new Reflection_Class($property->getType()->getElementTypeAsString());
					$properties = $class->getProperties();
					reset($value);
					if (!is_numeric(key($value))) {
						$value = arrayFormRevert($value);
					}
					foreach ($value as $key => $element) {
						foreach ($element as $property_name => $property_value) {
							if (isset($property_value) && isset($properties[$property_name])) {
								$value[$key][$property_name] = self::propertyToIso(
									$properties[$property_name], $property_value
								);
							}
						}
					}
				}
			}
			else {
				$value = self::propertyToIso($property, $value);
			}
		}
	}

	//----------------------------------------------------------------------- classNameDisplayReverse
	/**
	 * @param $value string class name taken from the import array
	 */
	public function classNameDisplayReverse(&$value)
	{
		if (isset($value)) {
			$value = explode(BS, $value);
			foreach ($value as $key => $class_part) {
				$value[$key] = Names::displayToClass(self::rtr($class_part));
			}
			$value = join(BS, $value);
		}
	}

	//--------------------------------------------------------------------------------------- context
	/**
	 * Gets a context option for the translation
	 *
	 * @param $context string
	 * @return Context
	 */
	public static function context($context)
	{
		return new Context($context);
	}

	//------------------------------------------------------------------------------------------ date
	/**
	 * Returns current date
	 *
	 * @return Date_Format
	 */
	public static function date()
	{
		return Locale::current()->date_format;
	}

	//------------------------------------------------------------------------------------- dateToIso
	/**
	 * Takes a locale date and make it ISO
	 *
	 * @param $date  string ie '12/25/2001' '12/25/2001 12:20' '12/25/2001 12:20:16'
	 * @param $max   boolean if true, the incomplete date will be completed to the max range
	 * eg '25/12/2001' will result in '2001-12-25 00:00:00' if false, '2001-12-25 23:59:59' if true
	 * @param $joker string if set, the character that replaces missing values, instead of current
	 * @return string ie '2001-12-25' '2001-12-25 12:20:00' '2001-12-25 12:20:16'
	 */
	public static function dateToIso($date, $max = false, $joker = null)
	{
		return Locale::current()->date_format->toIso($date, $max, $joker);
	}

	//---------------------------------------------------------------------------------- dateToLocale
	/**
	 * Takes an ISO date and make it locale
	 *
	 * @param $date string ie '2001-12-25' '2001-12-25 12:20:00' '2001-12-25 12:20:16'
	 * @return string '25/12/2011' '25/12/2001 12:20' '25/12/2001 12:20:16'
	 */
	public static function dateToLocale($date)
	{
		return Locale::current()->date_format->toLocale($date);
	}

	//---------------------------------------------------------------------------------------- enable
	/**
	 * @param $enabled boolean true to enable, false to disable
	 * @return boolean
	 */
	public static function enable(bool $enabled) : bool
	{
		$old_enabled     = static::$enabled;
		static::$enabled = $enabled;
		return $old_enabled;
	}

	//---------------------------------------------------------------------------------- enterContext
	/**
	 * Set current context for translations
	 *
	 * @param $context string
	 */
	public static function enterContext($context)
	{
		array_push(self::$contexts_stack, $context);
	}

	//----------------------------------------------------------------------------------- exitContext
	/**
	 * Exit current context for translations
	 */
	public static function exitContext()
	{
		array_pop(self::$contexts_stack);
	}

	//------------------------------------------------------------------------------------ floatToIso
	/**
	 * @param $float ?string
	 * @return ?float
	 * @throws User_Error_Exception
	 */
	public static function floatToIso(?string $float) : ?float
	{
		return Locale::current()->number_format->floatToIso($float);
	}

	//--------------------------------------------------------------------------------- floatToLocale
	/**
	 * Takes a float number and make it locale
	 *
	 * @param $float                 float ie 1000 1000.28 1000.2148
	 * @param $decimal_minimal_count integer|null if set, overrides decimal minimal count
	 * @param $decimal_maximal_count integer|null if set, overrides decimal maximal count
	 * @return string ie '1 000,00' '1 000,28' '1 000,2148'
	 */
	public static function floatToLocale(
		float $float, int $decimal_minimal_count = null, int $decimal_maximal_count = null
	) : string
	{
		return Locale::current()->number_format->floatToLocale(
			$float, $decimal_minimal_count, $decimal_maximal_count
		);
	}

	//------------------------------------------------------------------------------- formatTranslate
	/**
	 * @param $translate boolean
	 * @return boolean
	 */
	public static function formatTranslate($translate)
	{
		$locale                   = Locale::current();
		$format_translate         = $locale->format_translate;
		$locale->format_translate = $translate;
		return $format_translate;
	}

	//------------------------------------------------------------------------------------ getContext
	/**
	 * Returns the current valid context from the contexts stack
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string|null
	 */
	public static function getContext()
	{
		$context = end(self::$contexts_stack);
		/** @noinspection PhpUnhandledExceptionInspection class_exists */
		while (
			$context
			&& (
				is_a($context, Reflector::class, true)
				|| !(
					class_exists($context)
					&& (new Reflection_Class($context))->getAnnotation('business')->value
				)
			)
		) {
			$context = prev(self::$contexts_stack);
		}
		return $context;
	}

	//---------------------------------------------------------------------------------- integerToIso
	/**
	 * @param $integer ?string
	 * @return ?integer
	 * @throws User_Error_Exception
	 */
	public static function integerToIso(?string $integer) : ?int
	{
		return Locale::current()->number_format->integerToIso($integer);
	}

	//------------------------------------------------------------------------------- integerToLocale
	/**
	 * Takes an integer and make it locale
	 *
	 * @param $integer ?integer ie 1000
	 * @return string ie '1 000'
	 */
	public static function integerToLocale(?int $integer) : string
	{
		return Locale::current()->number_format->integerToLocale($integer);
	}

	//-------------------------------------------------------------------------------------- language
	/**
	 * Returns current language
	 *
	 * @return string
	 */
	public static function language()
	{
		return Locale::current()->language;
	}

	//-------------------------------------------------------------------------------- methodToLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing it's property
	 *
	 * @param $method Reflection_Method
	 * @param $value  mixed
	 * @return string
	 */
	public static function methodToLocale(Reflection_Method $method, $value)
	{
		return Locale::current()->methodToLocale($method, $value);
	}

	//--------------------------------------------------------------------------------- propertyToIso
	/**
	 * Change a locale value into an ISO formatted value, knowing it's property
	 *
	 * @param $property Reflection_Property
	 * @param $value    string
	 * @return mixed
	 */
	public static function propertyToIso(Reflection_Property $property, $value = null)
	{
		return Locale::current()->propertyToIso($property, $value);
	}

	//------------------------------------------------------------------------------ propertyToLocale
	/**
	 * Change an ISO value into a locale formatted value, knowing it's property
	 *
	 * @param $property Reflection_Property
	 * @param $value    mixed
	 * @return string
	 */
	public static function propertyToLocale(Reflection_Property $property, $value = null)
	{
		return Locale::current()->propertyToLocale($property, $value);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		// format from locale user input to ISO and standard formats
		$aop->beforeMethod(
			[Object_Builder_Array::class, 'buildBasicValue'],
			[$this, 'beforeObjectBuilderArrayBuildBasicValue']
		);
		// format to locale
		$aop->afterMethod(
			[Functions::class, 'toEditPropertyExtra'],
			[$this, 'afterHtmlTemplateFunctionsToEditPropertyExtra']
		);
		// translations
		$aop->afterMethod(
			[List_Setting\Set::class, 'getDefaultTitle'],
			[$this, 'translateReturnedValue']
		);
		// translation/reverse of export/import procedures
		$aop->beforeMethod(
			[Import_Array::class, 'getClassNameFromValue'],
			[$this, 'classNameDisplayReverse']
		);
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * @param $replace string[] key is the key for the replacement, value is the replacement value
	 * @return Replace
	 */
	public static function replace(array $replace)
	{
		return new Replace($replace);
	}

	//------------------------------------------------------------------------------------------- rtr
	/**
	 * Reverse translation
	 *
	 * @param $translation           string the translation to search for (can contain wildcards)
	 * @param $context               string if empty, use the actual context set by enterContext()
	 * @param $context_property_path string additional context : the property path
	 * @param $limit_to              string[] if set, limit texts to these results (when wildcards)
	 * @return string|string[]
	 */
	public static function rtr(
		$translation, $context = '', $context_property_path = '', array $limit_to = null
	) {
		if (!static::$enabled) {
			return $translation;
		}
		if (!$context) {
			$context = self::getContext();
		}
		return Locale::current()->translations->reverse(
			$translation, $context, $context_property_path, $limit_to
		);
	}

	//-------------------------------------------------------------------------------------------- tr
	/**
	 * Text translation
	 *
	 * @param $text     string The text to translate
	 * @param $options  Option[]|Has_Language[]|string[]|Option|Has_Language|string Options for
	 *        translation : see options in namespace ITRocks\Framework\Locale\Option
	 *        If options is a string or contain a string, this string is used as a context
	 *        If options contain a object who implements Has_Language, use object's language for
	 *        translation
	 * @return string The translated text
	 */
	public static function tr(string $text, array|object|string $options = []) : string
	{
		if (!is_array($options)) {
			$options = [$options];
		}
		if (!static::$enabled) {
			$translation = $text;
		}
		else {
			// For now, only 1 context is allowed, but to change
			$context  = '';
			$language = '';
			foreach ($options as $option) {
				if (is_string($option)) {
					// Compatibility with old usages of tr
					$context = $option;
				}
				elseif ($option instanceof Locale\Option\Context) {
					$context = $option->context;
				}
				elseif (isA($option, Has_Language::class)) {
					/** @var $option Has_Language */
					$language = $option->language->name;
				}
			}
			$old_language = '';
			if ($language) {
				$old_language                             = Locale::current()->translations->language;
				Locale::current()->translations->language = $language;
			}
			if (!$context) {
				$context = self::getContext();
			}
			$translation = Locale::current()->translations->translate($text, $context);
			if ($language) {
				// If we have change language
				Locale::current()->translations->language = $old_language;
			}
		}
		foreach ($options as $option) {
			if ($option instanceof Option) {
				$translation = $option->afterTranslation($translation);
			}
		}
		return $translation;
	}

	//------------------------------------------------------------------------ translateReturnedValue
	/**
	 * Translate returned value
	 *
	 * @param $result string
	 * @return string
	 */
	public function translateReturnedValue($result)
	{
		return self::tr($result);
	}

}
