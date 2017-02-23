<?php
namespace ITRocks\Framework\Locale\Tests;

use ITRocks\Framework\Locale\Translation_String_Composer;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tests\Test;

/**
 * Translation_String_Composer unit tests
 */
class Translation_String_Composer_Tests extends Test
{

	//--------------------------------------------------------------------------------------- enabled
	/**
	 * Returns true if the Translation_String_Composer plugin is enabled.
	 * Else false.
	 *
	 * @return boolean
	 */
	public function enabled()
	{
		return boolval(Session::current()->plugins->get(Translation_String_Composer::class));
	}

	//------------------------------------------------------------------------------------ testSimple
	public function testSimple()
	{
		$translator = new Translator();

		$translator->setTranslation('hello world', 'bienvenue monde');
		$this->assume(
			__METHOD__ . '.direct',
			$translator->translate('Hello world'),
			'Bienvenue monde'
		);

		$translator->setTranslation('hello $1', 'bienvenue $1');
		$translator->setTranslation('world',    'monde');
		$this->assume(
			__METHOD__ . '.sub-sentence ¦',
			$translator->translate('Hello ¦world¦'),
			'Bienvenue monde'
		);

		$translator->setTranslation('hello $!1', 'bienvenue $!1');
		$this->assume(
			__METHOD__ . '.ignore !',
			$translator->translate('Hello !world!'),
			'Bienvenue world'
		);

		$translator->setTranslation('hello $<1world$>1', 'bienvenue $<1monde$>1');
		$this->assume(
			__METHOD__ . '.ignore tags &lt;',
			$translator->translate('Hello <span class="world">world</span>'),
			'Bienvenue <span class="world">monde</span>'
		);

		$translator->setTranslation('hello', 'bienvenue');
		$translator->setTranslation('world', 'monde');
		$this->assume(
			__METHOD__ . '.sentence',
			$translator->translate('Hello. World.'),
			'Bienvenue. Monde.'
		);
	}

	//----------------------------------------------------------------------------------- testSpecial
	public function testSpecial()
	{
		$translator = new Translator();

		// Translate a sentence containing tags : tags are simplified
		$translator->setTranslation(
			'how to accumulate $<1exclamation and tag$>1',
			'comment cumuler $<1exclamation et élément html$>1'
		);
		$this->assume(
			__METHOD__ . htmlentities('.<>'),
			$translator->translate('How to accumulate <span class="what">exclamation and tag</span>'),
			'Comment cumuler <span class="what">exclamation et élément html</span>'
		);

		// The right way to ignore a tagged text
		$translator->setTranslation('how to accumulate $!1', 'comment cumuler $!1');
		$translator->setTranslation('exclamation and tag', 'exclamation et élément html');
		$this->assume(
			__METHOD__ . htmlentities('.!<>!'),
			$translator->translate('How to accumulate !<span class="what">exclamation and tag</span>!'),
			'Comment cumuler <span class="what">exclamation and tag</span>'
		);

		// This way is not the very best : more useless simplified tags in your translation data
		$translator->setTranslation('how to accumulate $<1$!1$>1', 'comment cumuler $<1$!1$>1');
		$this->assume(
			__METHOD__ . htmlentities('.<!!>'),
			$translator->translate('How to accumulate <span class="what">!exclamation and tag!</span>'),
			'Comment cumuler <span class="what">exclamation and tag</span>'
		);
	}

	//------------------------------------------------------------------------------- testThreeLevels
	public function testThreeLevels()
	{
		$translator = new Translator();

		$translator->setTranslation('my $1 is $2', 'mon $1 est $2');
		$translator->setTranslation('tailor $!1',  'tailleur $!1');
		$translator->setTranslation('rich',        'riche');
		$this->assume(
			__METHOD__ . htmlentities('.¦!<>!¦'),
			$translator->translate('My ¦tailor !Big <span class="name">Mike</span>!¦ is ¦rich¦'),
			'Mon tailleur Big <span class="name">Mike</span> est riche'
		);

		$translator->setTranslation('tailor $<1Big $!1$>1', 'tailleur $<1Gros $!1$>1');
		$this->assume(
			__METHOD__ . htmlentities('.¦<!!>¦'),
			$translator->translate('My ¦tailor <span class="who">Big !Mike!</span>¦ is ¦rich¦'),
			'Mon tailleur <span class="who">Gros Mike</span> est riche'
		);

		$translator->setTranslation('my $!1 is $1', '$1 est mon $!1');
		$this->assume(
			__METHOD__ . htmlentities('.!¦<>¦!'),
			$translator->translate('My !tailor ¦Big <span class="name">Mike</span>¦! is ¦rich¦'),
			'Riche est mon tailor ¦Big <span class="name">Mike</span>¦'
		);

		$this->assume(
			__METHOD__ . htmlentities('.!<¦¦>!'),
			$translator->translate('My !tailor <span class="who">Big ¦Mike¦</span>! is ¦rich¦'),
			'Riche est mon tailor <span class="who">Big ¦Mike¦</span>'
		);

		$translator->setTranslation('my $<1tailor $1$>1 is $2', 'mon $<1tailleur $1$>1 est $2');
		$translator->setTranslation('Big $!1',                  'Gros $!1');
		$this->assume(
			__METHOD__ . htmlentities('.<¦!!¦>'),
			$translator->translate('My <span class="who">tailor ¦Big !Mike!¦</span> is ¦rich¦'),
			'Mon <span class="who">tailleur Gros Mike</span> est riche'
		);

		$translator->setTranslation('my $<1tailor $!1$>1 is $1', '$1 est mon $<1tailleur $!1$>1');
		$this->assume(
			__METHOD__ . htmlentities('.<!¦¦!>'),
			$translator->translate('My <span class="who">tailor !Big ¦Mike¦!</span> is ¦rich¦'),
			'Riche est mon <span class="who">tailleur Big ¦Mike¦</span>'
		);
	}

	//--------------------------------------------------------------------------------- testTwoLevels
	public function testTwoLevels()
	{
		$translator = new Translator();

		$translator->setTranslation('hello $1', 'bonjour $1');
		$translator->setTranslation('dear $!1', 'cher $!1');
		$this->assume(
			__METHOD__ . htmlentities('.¦!!¦'),
			$translator->translate('Hello ¦dear !Mike!¦'),
			'Bonjour cher Mike'
		);

		$translator->setTranslation('dear $<1Mike$>1', 'cher $<1Mike$>1');
		$this->assume(
			__METHOD__ . htmlentities('.¦<>¦'),
			$translator->translate('Hello ¦dear <span class="name">Mike</span>¦'),
			'Bonjour cher <span class="name">Mike</span>'
		);

		$translator->setTranslation('hello $!1', 'bonjour $!1');
		$translator->setTranslation('Mike',      'Michel');
		$this->assume(
			__METHOD__ . htmlentities('.!¦¦!'),
			$translator->translate('Hello !dear ¦Mike¦!'),
			'Bonjour dear ¦Mike¦'
		);

		$this->assume(
			__METHOD__ . htmlentities('.!<>!'),
			$translator->translate('Hello !dear <span class="name">Mike</span>!'),
			'Bonjour dear <span class="name">Mike</span>'
		);

		$translator->setTranslation('hello $<1dear $1$>1', 'bonjour $<1cher $1$>1');
		$this->assume(
			__METHOD__ . htmlentities('.<¦¦>'),
			$translator->translate('Hello <span class="who">dear ¦Mike¦</span>'),
			'Bonjour <span class="who">cher Michel</span>'
		);

		$translator->setTranslation('hello $<1dear $!1$>1', 'bonjour $<1cher $!1$>1');
		$this->assume(
			__METHOD__ . htmlentities('<!!>'),
			$translator->translate('Hello <span class="who">dear !Mike!</span>'),
			'Bonjour <span class="who">cher Mike</span>'
		);
	}

}
