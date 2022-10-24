<?php
namespace ITRocks\Framework\Layout\Display_Model;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Layout\Display_Model;

/**
 * Display model generate controller
 */
class Generate_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'generate';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return string
	 */
	public function run(Parameters $parameters, array $form, array $files) : string
	{
		$display_model = $parameters->getMainObject(Display_Model::class);
		$exporter      = Builder::create(Output_Exporter::class);
		$object        = Builder::create($display_model->class_name);

		$generator = new Output_Generator($display_model, $exporter);
		$structure = $generator->generate($object);

		$exporter->structure = $structure;
		$exporter->exportHtml();

		return 'Generated';
	}

}
