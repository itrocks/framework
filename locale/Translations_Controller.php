<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

/**
 * Locale translations controller
 */
class Translations_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------- $language
	/**
	 * @var string
	 */
	public string $language;

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @var string
	 */
	public string $text;

	//------------------------------------------------------------------------------- parseParameters
	/**
	 * @output $this->language, $this->text
	 * @param $parameters Parameters optional ['text', 'language'] or [0=>, 1=>]
	 * @param $form       string[]   optional [string $text, string $language]
	 */
	protected function parseParameters(Parameters $parameters, array $form)
	{
		if (isset($form['text'])) {
			$this->text     = $form['text'];
			$this->language = $form['language'] ?? '';
		}
		elseif ($parameters->has('text')) {
			$this->text     = $parameters->getRawParameter('text');
			$this->language = $parameters->getRawParameter('language');
		}
		else {
			$this->text     = $parameters->shift();
			$this->language = $parameters->shift();
		}
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Launches translation
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return string
	 */
	public function run(Parameters $parameters, array $form, array $files) : string
	{
		$this->parseParameters($parameters, $form);
		return $this->translations();
	}

	//---------------------------------------------------------------------------------- translations
	/**
	 * @input $this->context, $this->text
	 * @return string
	 */
	protected function translations() : string
	{
		$translator   = new Translator($this->language);
		$translations = $translator->translations($this->text, true);
		return json_encode($translations);
	}

}
