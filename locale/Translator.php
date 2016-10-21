<?php
namespace SAF\Framework\Locale;

use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Mapper\Search_Object;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * Translations give the programmer translations features, and store them into cache
 *
 * TODO : translations maintainer : only one text per context, and only one translation per context
 */
class Translator
{

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * @var array string[][] $translation[$text][$context]
	 */
	protected $cache = [];

	//------------------------------------------------------------------------------------- $language
	/**
	 * @var string
	 */
	public $language;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $language string
	 */
	public function __construct($language = null)
	{
		if (isset($language)) {
			$this->language = $language;
		}
	}

	//----------------------------------------------------------------------------- chooseTranslation
	/**
	 * @param $translations string[]
	 * @param $context      string
	 * @return string
	 */
	private function chooseTranslation($translations, $context)
	{
		$translation = '';
		if (isset($translations['']) && $translations['']) {
			$translation = $translations[''];
			unset($translations['']);
		}
		if ($context) {
			foreach ($translations as $translation_context => $contextual_translation) {
				if ($contextual_translation && isA($context, $translation_context)) {
					$context     = $translation_context;
					$translation = $contextual_translation;
				}
			}
		}
		return $translation;
	}

	//---------------------------------------------------------------------------- defaultTranslation
	/**
	 * @param $text string
	 * @return string
	 */
	private function defaultTranslation($text)
	{
		return endsWith($text, AT) ? strUri(rtrim($text, AT)) : str_replace('_', SP, $text);
	}

	//--------------------------------------------------------------------------------------- reverse
	/**
	 * Reverse translator : changes a translated text into an original text
	 *
	 * @param $translation           string
	 * @param $context               string
	 * @param $context_property_path string ie 'property_name.sub_property', accepts (and ignore) '*'
	 * @return string
	 */
	public function reverse($translation, $context = '', $context_property_path = '')
	{
		if (!trim($translation) || is_numeric($translation)) {
			return $translation;
		}
		elseif (strpos($translation, DOT) !== false) {
			$text = [];
			foreach (explode(DOT, $translation) as $sentence) {
				$text[] = $this->reverse($sentence, $context, $context_property_path);
			}
			return join(DOT, $text);
		}
		$context_property = str_replace('*', '', $context_property_path);
		/** @var $search Translation */
		$search = Search_Object::create(Translation::class);
		$search->language = $this->language;
		$search->translation = strtolower($translation);
		$search->context = $context_property_path
			? (new Reflection_Property($context, $context_property))->final_class
			: $context;
		$texts = Dao::search($search);
		foreach ($texts as $text) if ($text->translation === $translation) break;
		while (isset($search->context) && $search->context && !isset($text)) {
			$i = strrpos($search->context, DOT);
			$search->context = $i ? substr($search->context, 0, $i) : '';
			$texts = Dao::search($search);
			foreach ($texts as $text) if ($text->translation === $translation) break;
		}
		if (!isset($text) && strpos($translation, ', ')) {
			$text_parts = [];
			foreach (explode(', ', $translation) as $translation_part) {
				$text_parts[] = $this->reverse($translation_part, $context, $context_property_path);
			}
			$text = Builder::create(Translation::class,
				[join(', ', $text_parts), $this->language, $context, $translation]
			);
		}
		$text = isset($text) ? $text->text : $translation;
		return empty($text) ? $text : (strIsCapitals($translation[0]) ? ucfirsta($text) : $text);
	}

	//------------------------------------------------------------------------- separatedTranslations
	/**
	 * @param $text      string
	 * @param $separator string
	 * @param $context   string
	 * @return string
	 */
	private function separatedTranslations($text, $separator, $context)
	{
		$translation = [];
		foreach (explode($separator, $text) as $sentence) {
			$translation[] = $this->translate($sentence, $context);
		}
		return join($separator, $translation);
	}

	//----------------------------------------------------------------------- storeDefaultTranslation
	/**
	 * @param $text string
	 * @return string
	 */
	private function storeDefaultTranslation($text)
	{
		/** @var $translation Translation */
		$translation = Builder::create(Translation::class, [rtrim($text, AT), $this->language]);
		Dao::write($translation);
		return $this->defaultTranslation($text);
	}

	//------------------------------------------------------------------------------------- translate
	/**
	 * Translates a text using current language and an optional given context
	 *
	 * @param $text    string
	 * @param $context string
	 * @return string
	 */
	public function translate($text, $context = '')
	{
		// no text : no translation
		if (!trim($text) || is_numeric($text)) {
			return $text;
		}
		// different texts separated by dots : translate each part between dots
		elseif (strpos($text, DOT) !== false) {
			return $this->separatedTranslations($text, DOT, $context);
		}
		// return cached contextual translation
		elseif (isset($this->cache[$text][$context])) {
			return $this->cache[$text][$context];
		}
		// $translations string[] $translation[$context]
		if (!isset($this->cache[$text])) {
			$this->cache[$text] = $this->translations($text);
		}
		$translations = $this->cache[$text];
		// no translation found and separated by commas : translate each part between commas
		if (!$translations && (strpos($text, ', ') !== false)) {
			return $this->separatedTranslations($text, ', ', $context);
		}
		// no translation found : store original text to cache and database, then return it
		if (!$translations) {
			$translations[''] = $this->cache[$text][''] = $this->storeDefaultTranslation($text);
		}
		$translation = $this->chooseTranslation($translations, $context)
			?: $this->defaultTranslation($text);
		$this->cache[$text][$context] = $translation;
		return strIsCapitals($text[0]) ? ucfirsta($translation) : $translation;
	}

	//---------------------------------------------------------------------------------- translations
	/**
	 * @param $text string
	 * @return string[] $translation[$context]
	 */
	private function translations($text)
	{
		if (endsWith($text, AT)) {
			$str_uri = true;
			$text = rtrim($text, AT);
		}
		/** @var $translations Translation[] */
		$translations = Dao::search(
			['language' => $this->language, 'text' => $text], Translation::class, [Dao::key('context')]
		);
		foreach ($translations as $context => $translation) {
			$translations[$context] = isset($str_uri)
				? strUri($translation->translation)
				: $translation->translation;
		}
		/** @var $translations string[] */
		return $translations;
	}

}
