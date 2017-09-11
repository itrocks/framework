<?php
namespace ITRocks\Framework\Widget\Confirm;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Button;
use ITRocks\Framework\Widget\Output\Output_Controller;

/**
 * Class Confirm_Controller.
 */
class Confirm_Controller extends Output_Controller implements Button\Has_General_Buttons
{

	//------------------------------------------------------------------------------- CONFIRM_MESSAGE
	/**
	 * Name of the $_GET parameter storing the confirmation message to display to the user.
	 */
	const CONFIRM_MESSAGE = '_message';

	//---------------------------------------------------------------------------------------- TARGET
	/**
	 * Name of the $_GET parameter storing the target URL.
	 */
	const TARGET = '_target';

	//-------------------------------------------------------------------------- createGeneralButtons
	/**
	 * Build confirm & cancel buttons.
	 *
	 * @param $parameters Parameters
	 * @return Button[]
	 */
	private function createGeneralButtons(Parameters $parameters)
	{
		$confirm_button = new Button(
			Loc::tr('Confirm'),
			$parameters->getRawParameter(self::TARGET),
			'bulkSetStatus',
			[Target::MESSAGES]
		);
		$confirm_button->class = 'submit';

		// Simply close confirm dialog on click.
		$cancel_button = new Button(
			Loc::tr('Cancel'),
			'javascript:ConfirmDialog._closeDialog()',
			Feature::F_DELETE,
			[Target::MESSAGES]
		);

		return [$confirm_button, $cancel_button];
	}

	//------------------------------------------------------------------------------- extractPostData
	/**
	 * Format a multidimensional array into a simple array of strings formatted as following:
	 * array(
	 *      "foo[bar][foo]" => "bar",
	 *      "bar[foo]"      => "dummy",
	 *      "foo"           => "bar",
	 * )
	 *
	 * @param $data array The data to format.
	 *
	 * @return array
	 */
	public function extractPostData(array $data)
	{
		$data_string = http_build_query($data, null, '||');
		$result      = explode('||', urldecode($data_string));

		foreach ($result as $key => $value) {
			$item = explode('=', $value);

			if (count($item) === 2) {
				$result[$item[0]] = $item[1];
				unset($result[$key]);
			}
		}

		return $result;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * {@inheritdoc}
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		$confirm_message = $parameters->getRawParameter(self::CONFIRM_MESSAGE)
			?: Loc::tr('Do you confirm this action ?');

		$params                        = $parameters->toGet();
		$params['title']               = $confirm_message;
		$params['form_data']           = $this->extractPostData($_POST);
		$params[self::GENERAL_BUTTONS] = $this->createGeneralButtons($parameters);

		return View::run($params, $form, $files, $class_name, Feature::F_CONFIRM);
	}

}
