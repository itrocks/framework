<?php
namespace SAF\Framework;

/**
 * Import execution controller
 */
class Import_Execute_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$timer = new Execution_Timer();
		$parameters = $parameters->getObjects();
		$import = Import_Builder_Form::build($form);
		foreach ($import->worksheets as $worksheet) {
			$array = $worksheet->getCsvContent();
			(new Import_Array($worksheet->settings))->importArray($array);
		}
		echo "duration = " . $timer->end() . "<br>";
		return View::run($parameters, $form, $files, 'SAF\Framework\Import', "execute");
	}

}
