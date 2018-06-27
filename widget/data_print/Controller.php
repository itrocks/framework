<?php
namespace ITRocks\Framework\Widget\Data_Print;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
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

		// TODO remove this debug file
		file_put_contents('/tmp/structure.txt', $structure->dump());

		$file_name = Names::classToDisplay($layout_model->class_name) . '.pdf';
		return $pdf->Output($file_name, PDF\Output::INLINE);
	}

}
