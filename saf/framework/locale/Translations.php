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
class Translations
{

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * @var string[]
	 */
	public $cache = [];

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
		if (empty($translation)) {
			return $translation;
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
	 * Translates a text using current language and an optionnal given context
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
		elseif (!isset($this->cache[$text]) || !isset($this->cache[$text][$context])) {
			if (substr($text, -1) === AT) {
				$str_uri = true;
				$text = substr($text, 0, -1);
			}
			else {
				$str_uri = false;
			}
			$search = Builder::create(Translation::class, [$text, $this->language, $context]);
			$translations = Dao::search($search);
			foreach ($translations as $translation) if ($translation->text === $text) break;
			while ($search->context && !isset($translation)) {
				$i = strrpos($search->context, DOT);
				$search->context = $i ? substr($search->context, 0, $i) : '';
				$translations = Dao::search($search);
				foreach ($translations as $translation) if ($translation->text === $text) break;
			}
			if (!isset($translation) && strpos($text, ', ')) {
				$translation_parts = [];
				foreach (explode(', ', $text) as $text_part) {
					$translation_parts[] = $this->translate($text_part, $context);
				}
				$translation = Builder::create(Translation::class, [
					$text, $this->language, $context, join(', ', $translation_parts)
				]);
			}
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
		}
		$translation = $this->cache[$text][$context];
		return empty($translation)
			? $text
			: (strIsCapitals($text[0]) ? ucfirsta($translation) : $translation);
	}

}
