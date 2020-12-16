<?php
namespace ITRocks\Framework\Locale\Tests;

use ITRocks\Framework\Locale\Translation_String_Composer;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Tests\Test;

/**
 * Translation_String_Composer unit tests
 */
class Translation_String_Composer_Test extends Test
{

	//---------------------------------------------------------------------------------- TRANSLATIONS
	const TRANSLATIONS = [
		'Big $!1'                                     => 'Gros $!1',
		'dear $!1'                                    => 'cher $!1',
		'dear $<1Mike$>1'                             => 'cher $<1Mike$>1',
		'exclamation and tag'                         => 'exclamation et élément html',
		'hello'                                       => 'bonjour',
		'hello world'                                 => 'bonjour monde',
		'hello $1'                                    => 'bonjour $1',
		'hello $!1'                                   => 'bonjour $!1',
		'hello $<1world$>1'                           => 'bonjour $<1monde$>1',
		'hello $<1dear $1$>1'                         => 'bonjour $<1cher $1$>1',
		'hello $<1dear $!1$>1'                        => 'bonjour $<1cher $!1$>1',
		'how to accumulate $<1exclamation and tag$>1' => 'comment cumuler $<1exclamation et élément html$>1',
		'how to accumulate $!1'                       => 'comment cumuler $!1',
		'how to accumulate $<1$!1$>1'                 => 'comment cumuler $<1$!1$>1',
		'Mike'                                        => 'Michel',
		'my $1 is $2'                                 => 'mon $1 est $2',
		'my $!1 is $1'                                => '$1 est mon $!1',
		'my $<1tailor $1$>1 is $2'                    => 'mon $<1tailleur $1$>1 est $2',
		'my $<1tailor $!1$>1 is $1'                   => '$1 est mon $<1tailleur $!1$>1',
		'rich'                                        => 'riche',
		'tailor $!1'                                  => 'tailleur $!1',
		'tailor $<1Big $!1$>1'                        => 'tailleur $<1Gros $!1$>1',
		'world'                                       => 'monde',
	];

	//----------------------------------------------------------------------------------- $translator
	/**
	 * @var Translator
	 */
	public $translator;

	//----------------------------------------------------------------------------- providerTranslate
	/**
	 * @return array
	 * @see testTranslate
	 */
	public function providerTranslate()
	{
		return [
			'direct'                              => ['bonjour monde', 'hello world'],
			'direct with caps'                    => ['Bonjour monde', 'Hello world'],
			'sub-sentence ¦'                      => ['bonjour monde', 'hello ¦world¦'],
			'ignore !'                            => ['bonjour world', 'hello !world!'],
			'ignore tags & lt;'                   => ['bonjour <span class="world">monde</span>', 'hello <span class="world">world</span>'],
			'multiple - sentences'                => ['bonjour . Monde . ', 'hello . World . '],
			'sentences - with - various - spaces' => ["Bonjour \t. \n  Monde\t\n.", "Hello \t. \n  World\t\n."],
			'special <>'                          => ['Comment cumuler <span class="what">exclamation et élément html</span>', 'How to accumulate <span class="what">exclamation and tag</span>'],
			'special !<>!'                        => ['Comment cumuler <span class="what">exclamation and tag</span>',         'How to accumulate !<span class="what">exclamation and tag</span>!'],
			// This way is not the very best : more useless simplified tags in your translation data
			'special <!!>'                        => ['Comment cumuler <span class="what">exclamation and tag</span>',         'How to accumulate <span class="what">!exclamation and tag!</span>'],
			'3 levels a ¦!<>!¦'                   => ['Mon tailleur Big <span class="name">Mike</span> est riche',  'My ¦tailor !Big <span class="name">Mike</span>!¦ is ¦rich¦'],
			'3 levels b ¦ < !!>¦'                 => ['Mon tailleur <span class="who">Gros Mike</span> est riche',  'My ¦tailor <span class="who">Big !Mike!</span>¦ is ¦rich¦'],
			'3 levels c !¦ <> ¦!'                 => ['Riche est mon tailor ¦Big <span class="name">Mike</span>¦',  'My !tailor ¦Big <span class="name">Mike</span>¦! is ¦rich¦'],
			'3 levels d !<¦¦>!'                   => ['Riche est mon tailor <span class="who">Big ¦Mike¦</span>',   'My !tailor <span class="who">Big ¦Mike¦</span>! is ¦rich¦'],
			'3 levels e <¦!!¦>'                   => ['Mon <span class="who">tailleur Gros Mike</span> est riche',  'My <span class="who">tailor ¦Big !Mike!¦</span> is ¦rich¦'],
			'3 levels f <!¦¦!>'                   => ['Riche est mon <span class="who">tailleur Big ¦Mike¦</span>', 'My <span class="who">tailor !Big ¦Mike¦!</span> is ¦rich¦'],
			'2 levels a ¦!!¦'                     => ['Bonjour cher Mike', 'Hello ¦dear !Mike!¦'],
			'2 levels b !¦¦!'                     => ['Bonjour dear ¦Mike¦',                          'Hello !dear ¦Mike¦!'],
			'2 levels c ¦ <> ¦'                   => ['Bonjour cher <span class="name">Mike</span>',  'Hello ¦dear <span class="name">Mike</span>¦'],
			'2 levels d <!!>'                     => ['Bonjour <span class="who">cher Mike</span>',   'Hello <span class="who">dear !Mike!</span>'],
			'2 levels e <¦¦>'                     => ['Bonjour <span class="who">cher Michel</span>', 'Hello <span class="who">dear ¦Mike¦</span>'],
			'2 levels f !<>!'                     => ['Bonjour dear <span class="name">Mike</span>',  'Hello !dear <span class="name">Mike</span>!'],
		];
	}

	//----------------------------------------------------------------------------------------- setUp
	public function setUp() : void
	{
		parent::setUp();
		if (!Translation_String_Composer::registered()) {
			static::markTestSkipped(Translation_String_Composer::class . ' plugin is not activated');
		}

		$this->translator = new Translator();
		foreach (static::TRANSLATIONS as $source => $translation) {
			$this->translator->setTranslation($source, $translation);
		}
	}

	//--------------------------------------------------------------------------------- testTranslate
	/**
	 * @dataProvider providerTranslate
	 * @param $expected string
	 * @param $source   string
	 */
	public function testTranslate($expected, $source)
	{
		static::assertEquals($expected, $this->translator->translate($source));
	}

}
