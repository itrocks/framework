<?php
namespace ITRocks\Framework\Feature\Import;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\File\Session_File\Files;
use ITRocks\Framework\Session;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * Import execution controller
 */
class Import_Execute_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		upgradeMemoryLimit('6G');
		upgradeTimeLimit(900);

		$import = Import_Builder_Form::build(
			$form, Session::current()->get(Files::class)->files
		);
		$import->class_name = $class_name;
		/** @noinspection PhpUnhandledExceptionInspection object */
		$parameters->getMainObject($import);
		/** @noinspection PhpUnhandledExceptionInspection no object */
		$parameters = $parameters->getObjects();
		foreach ($import->worksheets as $worksheet) {
			$array        = $worksheet->file->getCsvContent();
			$import_array = new Import_Array($worksheet->settings, $import->class_name);
			try {
				$import_array->importArray($array);
			}
			catch (Import_Exception $exception) {
				$parameters['detail']           = $exception->getMessage() ?: $exception->view_result;
				$parameters[Template::TEMPLATE] = 'importError';
			}
		}
		if (!isset($parameters[Template::TEMPLATE])) {
			$parameters[Template::TEMPLATE] = 'importDone';
		}
		return View::run($parameters, $form, $files, $class_name, Feature::F_IMPORT);
	}

}
