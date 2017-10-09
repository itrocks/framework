<?php
namespace ITrocks\Framework\View\Json;

use Exception;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\View\Html\Template;

/**
 * handles HTTP request with Accept: application/json to return json instead of html
 *
 * It runs like Html\Default_View in the way it uses template files to process json string
 * The template is a php file with .json.inc extension.
 */
class Default_View
{
	//------------------------------------------------------------------ JSON_TEMPLATE_FILE_EXTENSION
	/** extension without the dot
	 * Eg: 'json.inc' for a file myTemplate.json.inc
	 */
	const JSON_TEMPLATE_FILE_EXTENSION = 'json.inc';

	//----------------------------------------------------------------------------------------- $json
	/**
	 * @var string the result to output as a json encoded string
	 */
	public $json = false;

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters   array
	 * @param $form         array
	 * @param $files        array[]
	 * @param $class_name   string
	 * @param $feature_name string
	 * @return string
	 */
	public function run(array $parameters, array $form, array $files, $class_name, $feature_name)
	{
		if (!Engine::acceptJson()) {
			return null;
		}

		$feature_names
			= (isset($parameters[Feature::FEATURE]) && ($parameters[Feature::FEATURE] !== $feature_name))
			? [$parameters[Feature::FEATURE], $feature_name]
			: [$feature_name];

		//get the json file template
		$template_file = Engine::getTemplateFile(
			$class_name,
			$feature_names,
			isset($parameters[Template::TEMPLATE]) ? $parameters[Template::TEMPLATE] : null,
			static::JSON_TEMPLATE_FILE_EXTENSION
		);
		if (!$template_file) {
			header('HTTP/1.0 404 Not Found', true, 404);
			return 'null';
		}

		$this->json = false;;
		try {
			// create a closure bound to this object
			// it simply includes (php meaning) the template where context is this object and it has only
			// access to the run method arguments.
			// The template file should call
			$closure = function($template_file)
				use ($parameters, $form, $files, $class_name, $feature_name)
			{
					include($template_file);
			};
			$closure($template_file);
		}
		catch (Exception $exception) {
			$this->json = false;
		}

		if (($this->json === '') || ($this->json === false)) {
			header('HTTP/1.0 520 Unknown Error', true, 520);
			return 'null';
		}

		header('Content-Type: application/json; charset=utf-8');
		return $this->json;
	}

}
