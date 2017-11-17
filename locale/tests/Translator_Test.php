<?php
namespace ITRocks\Framework\Locale\Tests;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Language;
use ITRocks\Framework\Locale\Translation;
use ITRocks\Framework\Tests\Objects\Document;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Objects\Quote;
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

	//----------------------------------------------------------------------- testTranslateWithPlural
	public function testTranslateWithPlural()
	{
		$this->method(__METHOD__);
		$text = 'the text to translate';
		$translator = new Translator();
		$translator->setCache([
			$text => [
				''                    => 'the default text',
				'*'                   => 'the default texts',
				Document::class       => 'the document text',
				Document::class . '*' => 'the document texts',
				Order::class          => 'the order text',
				Order::class . '*'    => 'the order texts',
				Quote::class          => 'the quote text'
			]
		]);
		// test
		$this->assume('no-context', $translator->translate($text, ''),  'the default text');
		$this->assume('no-context, plural', $translator->translate($text, '*'), 'the default texts');
		$this->assume(
			'single with context',
			$translator->translate($text, Document::class),
			'the document text'
		);
		$this->assume(
			'plural with context',
			$translator->translate($text, Document::class . '*'),
			'the document texts'
		);
		$this->assume(
			'inherited',
			$translator->translate($text, Order::class),
			'the order text'
		);
		$this->assume(
			'inherited plural',
			$translator->translate($text, Order::class . '*'),
			'the order texts'
		);
		$this->assume(
			'inherited set',
			$translator->translate($text, Quote::class),
			'the quote text'
		);
		// when no plural : prefer using the parent plural than getting the current class singular
		$this->assume(
			'inherited not set',
			$translator->translate($text, Quote::class . '*'),
			'the document texts'
		);
		// reset cache
		$translator->setCache([]);
	}

}
