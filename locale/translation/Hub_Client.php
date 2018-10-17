<?php
namespace ITRocks\Framework\Locale\Translation;

use ITRocks\Framework\AOP\Joinpoint\Around_Method;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Translation;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * Translation hub client
 */
class Hub_Client implements Registerable
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * @var string
	 */
	protected $context = '';

	//------------------------------------------------------------------ getDefaultTranslationFromHub
	/**
	 * @param $object    Translator
	 * @param $text      string
	 * @param $joinpoint Around_Method
	 * @return string
	 */
	public function getDefaultTranslationFromHub(Translator $object, $text, Around_Method $joinpoint)
	{
		$translator = $object;

		$https_context   = stream_context_create(['https' => ['timeout' => 1]]);
		$hub_translation = file_get_contents(
			'https://hub.itrocks.org/hub/ITRocks/Framework/Locale/translate'
				. '?text=' . rawurlencode($text)
				// this str_replace is a patch : I don't understand why context is Some.Class.Path with dots
				. '&context=' . rawurlencode(str_replace(DOT, BS, $this->context))
				. '&language=' . $translator->language
				. '&format=json',
			false,
			$https_context
		);
		$hub_translation = json_decode($hub_translation);

		if ($hub_translation && isset($hub_translation->translation)) {
			$translation              = Builder::create(Translation::class);
			$translation->context     = $hub_translation->context;
			$translation->language    = $translator->language;
			$translation->text        = $text;
			$translation->translation = $hub_translation->translation;

			// this patch to avoid any problem with 'Translation beginning with an uppercase letter'
			if (
				ctype_upper($translation->text[0])
				&& !ctype_upper($translation->text[1])
				&& ctype_upper($translation->translation[0])
				&& !ctype_upper($translation->translation[1])
			) {
				$translation->text[0]        = strtolower($translation->text[0]);
				$translation->translation[0] = strtolower($translation->translation[0]);
			}

			Dao::write($translation);
			return $hub_translation->translation;
		}

		return $joinpoint->process($text);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->beforeMethod([Translator::class, 'translate'], [$this, 'setContext']);
		$register->aop->aroundMethod(
			[Translator::class, 'storeDefaultTranslation'], [$this, 'getDefaultTranslationFromHub']
		);
	}

	//------------------------------------------------------------------------------------ setContext
	/**
	 * @param $context string
	 */
	public function setContext($context)
	{
		$this->context = $context;
	}

}
