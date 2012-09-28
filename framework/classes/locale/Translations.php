<?php
namespace SAF\Framework;

class Translations extends Set
{

	/**
	 * @var multitype:string
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
			$translation = Dao::searchOne($search);
			while ($search->context && !$translation) {
				$i = strrpos($search->context, ".");
				$search->context = $i ? substr($search->context, 0, $i) : "";
				$translation = Dao::searchOne($search);
			}
			if (!$translation) {
				$translation = $search;
				$translation->translation = "";
				Dao::write($translation);
			}
			$translation = $translation ? $translation->translation : $text;
			$this->cache[$text][$context] = $translation;
		}
		return $this->cache[$text][$context];
	}

}
