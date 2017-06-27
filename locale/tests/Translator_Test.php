<?php
namespace ITRocks\Framework\Locale\Tests;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Language;
use ITRocks\Framework\Locale\Translation;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Tests\Test;

/**
 * Translator class tests
 */
class Translator_Test extends Test
{

	//--------------------------------------------------------------------------------- testTranslate
	public function testTranslate()
	{
		$this->method(__METHOD__);

		Dao::begin();

		// purge before first test
		foreach (Dao::search(['text' => 'Def translation text'], Translation::class) as $translation) {
			Dao::delete($translation);
		}

		// first test : for a non-existing translation
		$translator = new Translator(Language::FR);
		$this->assume(
			'user', $translator->translate('Def translation test', static::class), 'Def translation test'
		);
		$this->assume(
			'user', $translator->translate('Def translation test'), 'Def translation test'
		);

		// second test : with translations in database
		$translator = new Translator(Language::FR);
		$this->assume(
			'user', $translator->translate('Def translation test', static::class), 'Def translation test'
		);
		$this->assume(
			'user', $translator->translate('Def translation test'), 'Def translation test'
		);

		// purge for next test
		foreach (Dao::search(['text' => 'Def translation text'], Translation::class) as $translation) {
			Dao::delete($translation);
		}

		// did some queries but do not valid them
		Dao::rollback();
	}

}
