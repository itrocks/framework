<?php
namespace SAF\Framework;

/**
 * Translations give the programmer translations features, and store them into cache
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
	 * @param $translation string
	 * @param $context     string
	 * @return string
	 */
	public function reverse($translation, $context = "")
	{
		if (empty($translation)) {
			return $translation;
		}
		/** @var $search Translation */
		$search = Search_Object::create('SAF\Framework\Translation');
		$search->translation = strtolower($translation);
		$search->language = $this->language;
		/** @var $texts Translation[] */
		$texts = Dao::search($search);
		foreach ($texts as $text) if ($text->translation === $translation) break;
		$text = isset($text) ? $text->text : $translation;
		return $text;
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
		if (empty($text)) {
			return $text;
		}
		elseif (strpos($text, ".") !== false) {
			$translation = array();
			foreach (explode(".", $text) as $sentence) {
				$translation[] = $this->translate($sentence, $context);
			}
			return implode(".", $translation);
		}
		elseif (!isset($this->cache[$text]) || !isset($this->cache[$text][$context])) {
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
			$this->cache[$text][$context] = $translation;
		}
		$translation = $this->cache[$text][$context];
		if (strlen($translation)) {
			if (strIsCapitals($text[0])) {
				return ucfirsta($translation);
			}
			else {
				return $translation;
			}
		}
		else {
			return $text;
		}
	}

}
