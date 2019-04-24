<?php
namespace ITRocks\Framework\Locale\Tests;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Translation;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Tests\Objects\Document;
use ITRocks\Framework\Tests\Objects\Order;
use ITRocks\Framework\Tests\Objects\Quote;
use ITRocks\Framework\Tests\Test;
use ReflectionObject;

/**
 * Translator class tests
 *
 * @group functional
 */
class Translator_Test extends Test
{

	//------------------------------------------------------------------------------------------ TEST
	const TEST = 'Def translation test';

	//----------------------------------------------------------------------------------- $translator
	/**
	 * @var Translator
	 */
	public $translator;

	//---------------------------------------------------------------------------- setTranslatorCache
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $translator Translator
	 * @param $cache      array
	 */
	public static function setTranslatorCache(Translator $translator, $cache)
	{
		$reflection_translator = new ReflectionObject($translator);
		/** @noinspection PhpUnhandledExceptionInspection valid property */
		$cache_property        = $reflection_translator->getProperty('cache');
		$cache_property->setAccessible(true);
		$cache_property->setValue($translator, $cache);
	}

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		$this->translator = new Translator();

		parent::setUp();
		Dao::begin();

		// purge before first test
		foreach (Dao::search(['text' => 'Def translation text'], Translation::class) as $translation) {
			Dao::delete($translation);
		}
	}

	//-------------------------------------------------------------------------------------- tearDown
	/**
	 * {@inheritdoc}
	 */
	protected function tearDown()
	{
		parent::tearDown();
		Dao::rollback();
	}

	//--------------------------------------------------------------------------------- testTranslate
	public function testTranslate()
	{
		// first test : for a non-existing translation
		static::assertEquals(self::TEST, $this->translator->translate(self::TEST, static::class));
		static::assertEquals(self::TEST, $this->translator->translate(self::TEST));

		// second test : with translations in database
		static::assertEquals(self::TEST, $this->translator->translate(self::TEST, static::class));
		static::assertEquals(self::TEST, $this->translator->translate(self::TEST));
	}

	//----------------------------------------------------------------------- testTranslateWithPlural
	/**
	 * @dataProvider testTranslateWithPluralProvider
	 * @param $expected string
	 * @param $context  array
	 */
	public function testTranslateWithPlural($expected, $context)
	{
		$text = 'the text to translate';
		static::setTranslatorCache(
			$this->translator,
			[
				$text => [
					''                    => 'the default text',
					'*'                   => 'the default texts',
					Document::class       => 'the document text',
					Document::class . '*' => 'the document texts',
					Order::class          => 'the order text',
					Order::class . '*'    => 'the order texts',
					Quote::class          => 'the quote text'
				]
			]
		);
		// test
		static::assertEquals($expected, $this->translator->translate($text, $context));
	}

	//--------------------------------------------------------------- testTranslateWithPluralProvider
	/**
	 * @see testTranslateWithPlural
	 * return array
	 */
	public function testTranslateWithPluralProvider()
	{
		return [
			'no-context'          => ['the default text', ''],
			'no-context, plural'  => ['the default texts', '*'],
			'single with context' => ['the document text', Document::class],
			'plural with context' => ['the document texts', Document::class . '*'],
			'inherited'           => ['the order text', Order::class],
			'inherited plural'    => ['the order texts', Order::class . '*'],
			'inherited set'       => ['the quote text', Quote::class],
			// when no plural : prefer using the parent plural than getting the current class singular
			'inherited not set'   => ['the document texts', Quote::class . '*']
		];
	}

}
