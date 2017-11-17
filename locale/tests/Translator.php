<?php
namespace ITRocks\Framework\Locale\tests;

use ITRocks\Framework\Locale;

/**
 * Translator with setCache()
 */
class Translator extends Locale\Translator
{

	//-------------------------------------------------------------------------------------- setCache
	/**
	 * @param $cache array
	 */
	public function setCache(array $cache)
	{
		$this->cache = $cache;
	}

}
