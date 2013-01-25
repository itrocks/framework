<?php
namespace SAF\Framework;

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
	public function __construct($language)
	{
		$this->language = $language;
	}

	//--------------------------------------------------------------------------------------- reverse
	public function reverse($translation, $context = "")
	{
		// TODO
	}

	//------------------------------------------------------------------------------------- translate
	public function translate($text, $context = "")
	{
		if (!isset($this->cache[$text]) || !isset($this->cache[$text][$context])) {
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
			if (($text[0] >= 'A') && ($text[0] <= 'Z')) {
				return ucfirst($translation);
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
