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
	 * @var array $translation[$text][$context]
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
		if (!trim($text) || is_numeric($text)) {
			return $text;
		}
		elseif (strpos($text, DOT) !== false) {
			$translation = [];
			foreach (explode(DOT, $text) as $sentence) {
				$translation[] = $this->translate($sentence, $context);
			}
			return join(DOT, $translation);
		}
		elseif (!isset($this->cache[$text])) {
			$this->cache[$text] = $this->translations($text);
		}
		$translations = $this->cache[$text];
		if (!$translations && strpos($text, ', ')) {
			$translation = [];
			foreach (explode(', ', $text) as $text_part) {
				$translation[] = $this->translate($text_part, $context);
			}
			return join(', ', $translation);
		}
		// no translation found : return original text
		if (!$translations) {
			/** @var $translation Translation */
			$translation = Builder::create(
				Translation::class, [str_replace('_', SP, strtolower($text)), $this->language, '', '']
			);
			Dao::write($translation);
			return $text;
		}

			/*
			if (!isset($translation)) {
				$translation = $search;
				$translation->text = str_replace('_', SP, strtolower($translation->text));
				$translation->translation = '';
				Dao::write($translation);
			}
			$translation = $translation ? $translation->translation : $text;
			if ($str_uri) {
				$text .= AT;
				$translation = strUri($translation);
			}
			$this->cache[$text][$context] = $translation;
			*/
		}
		$translation = $this->cache[$text][$context];
		return empty($translation)
			? $text
			: (strIsCapitals($text[0]) ? ucfirsta($translation) : $translation);
	}

	//---------------------------------------------------------------------------------- translations
	/**
	 * @param $text string
	 * @return string[] $translation[$context]
	 */
	private function translations($text)
	{
		if (substr($text, -1) === AT) {
			$str_uri = true;
			$text = substr($text, 0, -1);
		}
		/** @var $translations Translation[] */
		$translations = Dao::search(
			['language' => $this->language, 'text' => $text], Translation::class, [Dao::key('context')]
		);
		if (isset($str_uri)) {
			foreach ($translations as $translation) {
				$translation->text .= AT;
				$translation->translation = strUri($translation->translation);
			}
		}
		foreach ($translations as $context => $translation) {
			$translations[$context] = $translation->translation;
		}
		/** @var $translations string[] */
		return $translations;
	}

}
