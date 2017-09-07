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

	//---------------------------------------------------------------------------------------- TARGET
	const TARGET = '_target';

	//------------------------------------------------------------------------------- extractPostData
	/**
	 * @param array       $data
	 * @param null|string $base_key
	 * @return array
	 */
	private function extractPostData(array $data, $base_key = null)
	{
		$result = [];

		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$result = array_merge($result, $this->extractPostData($value, $key));
			}
			else {
				if (is_string($base_key)) {
					$key = $base_key."[$key]";
				}

				$result[$key] = $value;
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
		$confirm_button = new Button(
			Loc::tr('Confirm'),
			$parameters->getRawParameter(self::TARGET),
			'bulkSetStatus',
			[Target::MESSAGES]
		);
		$confirm_button->class = 'submit';
		$cancel_button  = new Button(
			Loc::tr('Cancel'),
			'javascript:ConfirmDialog._closeDialog()',
			Feature::F_DELETE,
			[Target::MESSAGES]
		);

		$parameters                        = $parameters->toGet();
		$parameters['title']               = Loc::tr('Do you confirm this action ?');
		$parameters['form_data']           = $this->extractPostData($_POST);
		$parameters[self::GENERAL_BUTTONS] = [$confirm_button, $cancel_button];

		return View::run($parameters, $form, $files, $class_name, Feature::F_CONFIRM);
	}

}
