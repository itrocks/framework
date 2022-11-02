<?php
namespace ITRocks\Framework\Feature\Print_;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Layout\Print_Model;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;

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
	protected function newLayoutModel(string $class_name)
	{
		Main::$current->redirect(
			View::link(
				Print_Model::class,
				Feature::F_ADD,
				['class_name' => Names::classToUri($class_name)]
			),
			Target::MAIN
		);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		$layout_model = $parameters->getObject(Print_Model::class);
		$parameters->remove(Print_Model::class);
		$objects = $parameters->getSelectedObjects($form);
		if ($layout_model) {
			/** @noinspection PhpUnhandledExceptionInspection */
			return Builder::create(Model::class)->print($objects, $layout_model);
		}
		$this->newLayoutModel($class_name);
		return '';
	}

}
