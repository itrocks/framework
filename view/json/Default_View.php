<?php
namespace ITrocks\Framework\View\Json;

use Exception;
use ITRocks\Framework\Controller\Feature;
use itrocks\framework\exception\Http_404_Exception;
use itrocks\framework\exception\Http_406_Exception;
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
	 */
	public function run(array $parameters, array $form, array $files, $class_name, $feature_name)
	{
		try {

			if (!Engine::acceptJson()) {
				throw new Http_406_Exception('No header Accept: application/json');
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
				throw new Http_404_Exception('No Json template found for the uri');
			}

			$this->json = false;

			$renderer_class_name = Names::fileToClass($template_file);
			if ($renderer_class_name && isA($renderer_class_name, Json_Template::class)) {
				/** @var $renderer Json_Template */
				if ($renderer = new $renderer_class_name(
					$parameters, $form, $files, $class_name, $feature_name
				)) {
					$this->json = $renderer->render();
				}
			}
		}
		catch (Http_404_Exception $exception) {
			$this->json = \GuzzleHttp\json_encode($exception->getMessage());
		}
		catch (Http_406_Exception $exception) {
			$this->json = \GuzzleHttp\json_encode($exception->getMessage());
		}
		catch (Exception $exception) {
			header('HTTP/1.0 520 Unknown Error', true, 520);
			$this->json = \GuzzleHttp\json_encode($exception->getMessage());
		}
		finally {
			header('Content-Type: application/json; charset=utf-8');
			return $this->json;
		}
	}

}
