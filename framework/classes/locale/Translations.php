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
			while ($search->context && !($translation = Dao::search($search))) {
				$i = strrpos($search->context, ".");
				$search->context = $i ? substr($search->context, 0, $i) : "";
				$translation = Dao::search($search);
			}
			if (!$translation) {
				$search->translation = $text;
				Dao::write($search);
			}
			$this->cache[$text][$context] = $translation;
		}
		return $this->cache[$text][$context];
	}

}
