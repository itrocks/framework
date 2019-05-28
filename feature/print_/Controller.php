<?php
namespace ITRocks\Framework\Feature\Print_;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\Export\PDF;
use ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\PDF\Exporter;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Tools\Names;
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

	//------------------------------------------------------------------------------- printUsingModel
	/**
	 * Print objects using a layout model
	 *
	 * @param $objects     object[]
	 * @param $print_model Print_Model
	 * @return mixed
	 */
	protected function printUsingModel(array $objects, Print_Model $print_model)
	{
		/** @var $pdf PDF|TCPDF */
		$pdf = new PDF();
		$pdf->Open();

		$structure = null;
		foreach ($objects as $object) {
			$exporter            = new Exporter();
			$exporter->pdf       = $pdf;
			$generator           = new Generator($print_model, $exporter);
			$structure           = $generator->generate($object);
			$exporter->structure = $structure;
			$exporter->appendToPdf();
		}

		$file_name = Names::classToDisplay($print_model->class_name) . '.pdf';
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
		/** @noinspection PhpUnhandledExceptionInspection constant */
		$layout_model = $parameters->getObject(Print_Model::class);
		$parameters->remove(Print_Model::class);

		$objects = $parameters->getSelectedObjects($form);

		return $layout_model
			? $this->printUsingModel($objects, $layout_model)
			: $this->newLayoutModel($class_name);
	}

}
