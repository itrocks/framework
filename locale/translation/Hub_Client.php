<?php
namespace ITRocks\Framework\Locale\Translation;

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

	//------------------------------------------------------------------------ getTranslationsFromHub
	/**
	 * @param $text   string
	 * @param $object Translator
	 * @param $result string[]
	 */
	public function getTranslationsFromHub(string $text, Translator $object, array &$result)
	{
		if (!$result) {

			$https_context = stream_context_create(['http' => ['timeout' => 3]]);
			/** @noinspection PhpUsageOfSilenceOperatorInspection Don't care if doesn't work */
			$translations = @file_get_contents(
				'https://hub.itrocks.org/hub/ITRocks/Framework/Locale/translations'
				. '?language=' . $object->language
				. '&text=' . rawurlencode($text),
				false,
				$https_context
			);
			$translations = json_decode($translations, true);
			if (!is_array($translations)) {
				$translations = [];
			}

			Dao::begin();
			foreach ($translations as $translation) {
				$translation = new Translation(
					$translation['text'],
					$translation['language'],
					$translation['context'],
					$translation['translation']
				);
				Dao::write($translation);
			}
			Dao::commit();

			if ($translations) {
				$result = $object->translations($text);
			}

		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[Translator::class, 'translations'], [$this, 'getTranslationsFromHub']
		);
	}

}
