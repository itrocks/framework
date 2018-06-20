<?php
namespace ITRocks\Framework\Widget\Data_Print;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Layout\Generator;
use ITRocks\Framework\Layout\Model;

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
		$object = $parameters->getMainObject();
		/** @noinspection PhpUnhandledExceptionInspection Object should always be found */
		$layout_model = $parameters->getObject(Model::class);

		$generator = new Generator($layout_model);
		$structure = $generator->generate($object);

		return '<section>' . PRE . print_r($structure, true) . _PRE . '</section>';
	}

}
