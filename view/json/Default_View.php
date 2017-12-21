<?php
namespace ITrocks\Framework\View\Json;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Exception\Http_403_Exception;
use ITRocks\Framework\Exception\Http_406_Exception;
use ITRocks\Framework\Exception\Http_Json_Exception;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View\Html\Template;

/**
 * Handles HTTP request with Accept: application/json to return json instead of html
 *
 * It runs like Html\Default_View in the way it uses template files to process json string
 * The template is a php file with .json.inc extension.
 */
class Default_View
{

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
	 * @throws Http_403_Exception
	 * @throws Http_406_Exception
	 * @throws Http_Json_Exception
	 */
	public function run(array $parameters, array $form, array $files, $class_name, $feature_name)
	{
		if (!Engine::acceptJson()) {
			throw new Http_406_Exception('No header Accept: application/json');
		}

		if ($feature_name == 'denied') {
			throw new Http_403_Exception("You don\'t have permission for this feature");
		}

		$feature_names
			= (isset($parameters[Feature::FEATURE]) && ($parameters[Feature::FEATURE] !== $feature_name))
			? [$parameters[Feature::FEATURE], $feature_name]
			: [$feature_name];

		//get the json file template
		$template_file = Engine::getTemplateFile(
			$class_name,
			$feature_names,
			(
				isset($parameters[Template::TEMPLATE])
				? Names::propertyToClass($parameters[Template::TEMPLATE])
				: null
			),
			Engine::JSON_TEMPLATE_FILE_EXTENSION
		);
		if (!$template_file) {
			throw new Http_406_Exception('No Json template found for the uri');
		}

		$this->json = false;

		$renderer_class_name = Names::fileToClass($template_file);
		if ($renderer_class_name && isA($renderer_class_name, Json_Template::class)) {
			/** @var $renderer Json_Template */
			if (
				$renderer = new $renderer_class_name($parameters, $form, $files, $class_name, $feature_name)
			) {
				$this->json = $renderer->render();
			}
		}

		if (!$this->json) {
			throw new Http_Json_Exception('Renderer class not found', 500);
		}

		header('Content-Type: application/json; charset=utf-8');
		return $this->json;
	}

}

