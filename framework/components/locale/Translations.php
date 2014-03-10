<?php
namespace SAF\Framework;

/**
 * Translations give the programmer translations features, and store them into cache
 *
 * TODO : translations maintainer : only one text per context, and only one translation per context
 */
class Translations extends Set
{

	//---------------------------------------------------------------------------------------- $cache
	/**
	 * @var string[]
	 */
	public $cache = array();

	//------------------------------------------------------------------------------------- $language
	/**
	 * @var string
	 */
	public $language;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $language string
	 */
	public function __construct($language)
	{
		$this->language = $language;
	}

	//--------------------------------------------------------------------------------------- reverse
	/**
	 * Reverse translator : changes a translated text into an original text
	 *
	 * @param $translation           string
	 * @param $context               string
	 * @param $context_property_path string ie "property_name.sub_property", accepts (and ignore) "*"
	 * @return string
	 */
	public function reverse($translation, $context = "", $context_property_path = "")
	{
		if (empty($translation)) {
			return $translation;
		}
		$context_property = str_replace("*", "", $context_property_path);
		/** @var $search Translation */
		$search = Search_Object::create('SAF\Framework\Translation');
		$search->language = $this->language;
		$search->translation = strtolower($translation);
		$search->context = $context_property_path
			? (new Reflection_Property($context, $context_property))->final_class
			: $context;
		$texts = Dao::search($search);
		foreach ($texts as $text) if ($text->translation === $translation) break;
		while (isset($search->context) && $search->context && !isset($text)) {
			$i = strrpos($search->context, ".");
			$search->context = $i ? substr($search->context, 0, $i) : "";
			$texts = Dao::search($search);
			foreach ($texts as $text) if ($text->translation === $translation) break;
		}
		$text = isset($text) ? $text->text : $translation;
		return empty($text) ? $text : (
			strIsCapitals($translation[0])
			? ucfirsta($text)
			: $text
		);
	}

	//------------------------------------------------------------------------------------- translate
	/**
	 * Translates a text using current language and an optionnal given context
	 *
	 * @param $text    string
	 * @param $context string
	 * @return string
	 */
	public function translate($text, $context = "")
	{
		if (!trim($text) || is_numeric($text)) {
			return $text;
		}
		elseif (strpos($text, ".") !== false) {
			$translation = array();
			foreach (explode(".", $text) as $sentence) {
				$translation[] = $this->translate($sentence, $context);
			}
			return join(".", $translation);
		}
		elseif (!isset($this->cache[$text]) || !isset($this->cache[$text][$context])) {
			if (substr($text, -1) === "@") {
				$str_uri = true;
				$text = substr($text, 0, -1);
			}
			else {
				$str_uri = false;
			}
			$search = new Translation($text, $this->language, $context);
			$translations = Dao::search($search);
			foreach ($translations as $translation) if ($translation->text === $text) break;
			while ($search->context && !isset($translation)) {
				$i = strrpos($search->context, ".");
				$search->context = $i ? substr($search->context, 0, $i) : "";
				$translations = Dao::search($search);
				foreach ($translations as $translation) if ($translation->text === $text) break;
			}
			if (!isset($translation)) {
				$translation = $search;
				$translation->translation = "";
				Dao::write($translation);
			}
			$translation = $translation ? $translation->translation : $text;
			if ($str_uri) {
				$text .= "@";
				$translation = strUri($translation);
			}
			$this->cache[$text][$context] = $translation;
		}
		$translation = $this->cache[$text][$context];
		return empty($translation) ? $text : (
			strIsCapitals($text[0])
			? ucfirsta($translation)
			: $translation
		);
	}

}
