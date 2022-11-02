<?php
namespace ITRocks\Framework\Feature\Validate;

use ITRocks\Framework\Controller\Default_Class_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * This controller enables object validation
 *
 * - checks if the object data follows all business rules,
 * - if there are errors, outputs an error view with error and warning messages,
 * - if there are warnings, outputs a confirmation view with warning messages
 * - if there are no errors/warnings or if the user confirmed, outputs a validated view
 * - for classes that have a "validated" status property, set its value to true
 */
class Controller implements Default_Class_Controller
{

	//-------------------------------------------------------------------------------------- VALIDATE
	const VALIDATE = 'validate';

	//------------------------------------------------------------------------------------------- run
	/**
	 * Default run method for the class controller, when no runFeatureName() method was found in it.
	 *
	 * Class controllers must implement this method if you want the controller to work.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		$object     = $parameters->getMainObject();
		$parameters = $parameters->getRawParameters();

		$validator = new Validator();
		$validator->validate($object);
		$parameters['validator'] = $validator;

		return View::run($parameters, $form, $files, get_class($object), self::VALIDATE);
	}

}
