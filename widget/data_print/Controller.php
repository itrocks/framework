<?php
namespace ITRocks\Framework\Widget\Data_Print;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Export\PDF;
use ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\Layout\PDF\Exporter;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Widget\Data_List\Selection;
use TCPDF;

/**
 * Print controller
 */
class Controller implements Default_Feature_Controller
{

	//-------------------------------------------------------------------------------- newLayoutModel
	/**
	 * No object selected, or no layout model : open a "new layout model" form
	 *
	 * @param $class_name string
	 */
	protected function newLayoutModel($class_name)
	{
		Main::$current->redirect(
			'/ITRocks/Framework/Layout/Model/add/class_name/' . Names::classToUri($class_name),
			Target::MAIN
		);
	}

	//------------------------------------------------------------------------- printUsingLayoutModel
	/**
	 * Print objects using a layout model
	 *
	 * @param $objects      object[]
	 * @param $layout_model Model
	 * @return mixed
	 */
	protected function printUsingLayoutModel(array $objects, Model $layout_model)
	{
		/** @var $pdf PDF|TCPDF */
		$pdf = new PDF();
		$pdf->Open();

		$structure = null;
		foreach ($objects as $object) {
			$generator = new Generator($layout_model);
			$structure = $generator->generate($object);
			$exporter  = new Exporter($structure);
			$exporter->appendToPdf($pdf);
		}

		$file_name = Names::classToDisplay($layout_model->class_name) . '.pdf';
		return $pdf->Output($file_name, PDF\Output::INLINE);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		$selection = new Selection($class_name);
		$selection->setFormData($form);
		$objects = $selection->readObjects();

		/** @noinspection PhpUnhandledExceptionInspection Object should always be found */
		$layout_model = $parameters->getObject(Model::class);

		return $layout_model
			? $this->printUsingLayoutModel($objects, $layout_model)
			: $this->newLayoutModel($class_name);
	}

}
