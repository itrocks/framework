<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

/**
 * Locale translate controller
 */
class Translate_Controller implements Feature_Controller
{

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
		if ($form) {
			$text    = $form['text'];
			$context = $form['context'];
		}
		else {
			$text    = $parameters->shift();
			$context = $parameters->shift();
		}
		$translator = new Html_Translator();
		return $translator->translateContent($text, $context);
	}

}
