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
	public string $context;

	//--------------------------------------------------------------------------------------- $format
	/**
	 * Format result using json.
	 *
	 * - If not formatted, will only return the translated text, without any other data
	 * - If formatted, the text and its read context will be returned
	 *
	 * @value json,
	 * @var string
	 */
	public string $format;

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
	 * @output $this->context, $this->language, $this->text
	 * @param $parameters Parameters optional ['text', 'context', 'language'] or [0=>, 1=>, 2=>]
	 * @param $form       string[]   optional [string $text, string $context, string $language]
	 */
	protected function parseParameters(Parameters $parameters, array $form)
	{
		if (isset($form['text'])) {
			$this->text     = $form['text'];
			$this->context  = $form['context']  ?? '';
			$this->language = $form['language'] ?? '';
			$this->format   = $form['format']   ?? '';
		}
		elseif ($parameters->has('text')) {
			$this->text     = $parameters->getRawParameter('text');
			$this->context  = $parameters->getRawParameter('context');
			$this->language = $parameters->getRawParameter('language');
			$this->format   = $parameters->getRawParameter('format');
		}
		else {
			$this->text     = $parameters->shift();
			$this->context  = $parameters->shift();
			$this->language = $parameters->shift();
			$this->format   = $parameters->shift();
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
	protected function setLanguage(string $language) : string
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

	//---------------------------------------------------------------------------------------- toJson
	/**
	 * @param $translation string
	 * @return string
	 */
	protected function toJson(string $translation) : string
	{
		$json = [
			'context'     => Locale::current()->translations->last_context,
			'translation' => $translation
		];
		return json_encode($json);
	}

	//------------------------------------------------------------------------------------- translate
	/**
	 * @input $this->context, $this->text
	 * @return string
	 */
	protected function translate() : string
	{
		if (str_contains($this->text, PIPE)) {
			$translator  = new Html_Translator();
			$translation = $translator->translateContent($this->text, $this->context);
		}
		else {
			$translation = Loc::tr($this->text, $this->context);
		}
		return ($this->format === 'json')
			? $this->toJson($translation)
			: $translation;
	}

}
