<?php
namespace ITRocks\Framework\Locale\Tests;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Number_Format;
use ITRocks\Framework\Locale\Translation;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Reflection\Type;
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
	public Translator $translator;

	//---------------------------------------------------------------------------- setTranslatorCache
	/**
	 * @param $translator Translator
	 * @param $cache      array
	 */
	public static function setTranslatorCache(Translator $translator, array $cache) : void
	{
		$reflection_translator = new ReflectionObject($translator);
		$cache_property        = $reflection_translator->getProperty('cache');
		$cache_property->setValue($translator, $cache);
	}

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * {@inheritdoc}
	 */
	protected function setUp() : void
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
	protected function tearDown() : void
	{
		parent::tearDown();
		Dao::rollback();
	}

	//---------------------------------------------------------------------------------- testToLocale
	/**
	 * @dataProvider toLocalProvider
	 * @param $expected string
	 * @param $value    mixed
	 * @param $type     Type|null
	 */
	public function testToLocale(string $expected, mixed $value, Type $type = null) : void
	{
		$local = new Locale([
			Locale::DATE     => 'd/m/Y',
			Locale::LANGUAGE => 'fr',
			Locale::NUMBER   => [
				Number_Format::DECIMAL_MINIMAL_COUNT => 2,
				Number_Format::DECIMAL_MAXIMAL_COUNT => 2,
				Number_Format::DECIMAL_SEPARATOR     => ',',
				Number_Format::THOUSAND_SEPARATOR    => ' ',
			]
		]);
		$locale_value = $local->toLocale($value, $type);
		$this->assertEquals($expected, $locale_value);
	}

	//--------------------------------------------------------------------------------- testTranslate
	public function testTranslate() : void
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
	 * @dataProvider translateWithPluralProvider
	 * @param $expected string
	 * @param $context  string
	 */
	public function testTranslateWithPlural(string $expected, string $context) : void
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

	//------------------------------------------------------------------------------- toLocalProvider
	public function toLocalProvider() : array
	{
		return [
			['91,85', '91.8500', new Type(Type::FLOAT)]
		];
	}

	//------------------------------------------------------------------- translateWithPluralProvider
	/**
	 * @see testTranslateWithPlural
	 * return array
	 */
	public function translateWithPluralProvider() : array
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
