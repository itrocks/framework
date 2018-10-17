<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Locale;

/**
 * Locale translate controller
 */
class Translate_Controller implements Feature_Controller
{

	//-------------------------------------------------------------------------------------- $context
	/**
	 * @var string
	 */
	public $context;

	//------------------------------------------------------------------------------------- $language
	/**
	 * @var string
	 */
	public $language;

	//----------------------------------------------------------------------------------------- $text
	/**
	 * @var string
	 */
	public $text;

	//------------------------------------------------------------------------------- parseParameters
	/**
	 * @output $this->context, $this->language, $this->text
	 * @param $parameters Parameters optional ['text', 'context', 'language'] or  ['t', 'c', 'l']
	 * @param $form       string[]   optional [string $text, string $context, string $language]
	 */
	protected function parseParameters(Parameters $parameters, $form)
	{
		if (isset($form['text'])) {
			$this->text     = $form['text'];
			$this->context  = isset($form['context'])  ? $form['context']  : '';
			$this->language = isset($form['language']) ? $form['language'] : '';
		}
		elseif ($parameters->has('text')) {
			$this->text     = $parameters->getRawParameter('text');
			$this->context  = $parameters->getRawParameter('context');
			$this->language = $parameters->getRawParameter('language');
		}
		elseif ($parameters->has('t')) {
			$this->text     = $parameters->getRawParameter('t');
			$this->context  = $parameters->getRawParameter('c');
			$this->language = $parameters->getRawParameter('l');
		}
		else {
			$this->text     = $parameters->shift();
			$this->context  = $parameters->shift();
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
	public function run(Parameters $parameters, array $form, array $files)
	{
		$this->parseParameters($parameters, $form);
		$old_language = $this->setLanguage($this->language);
		$translation  = $this->translate();
		$this->setLanguage($old_language);
		return $translation;
	}

	//----------------------------------------------------------------------------------- setLanguage
	/**
	 * @output Locale::current()->language
	 * @param $language string
	 * @return string
	 */
	protected function setLanguage($language)
	{
		if ($language) {
			$old_language = Locale::current()->language;
			if ($old_language !== $language) {
				Locale::current()->language = $language;
				return $old_language;
			}
		}
		return $language;
	}

	//------------------------------------------------------------------------------------- translate
	/**
	 * @input $this->context, $this->text
	 * @return string
	 */
	protected function translate()
	{
		if (strpos($this->text, PIPE)) {
			$translator  = new Html_Translator();
			$translation = $translator->translateContent($this->text, $this->context);
		}
		else {
			$translation = Loc::tr($this->text, $this->context);
		}
		return $translation;
	}

}
