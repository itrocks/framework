<?php
namespace ITRocks\Framework\Layout\Model\Export;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Layout\Model;
use ITRocks\Framework\Layout\Model\Export;

/**
 * Layout model export controller
 */
class Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return string
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function run(Parameters $parameters, array $form, array $files) : string
	{
		if ($form) {
			/** @var $models Model[] */
			$models = $parameters->getSelectedObjects($form);
		}
		else {
			$model  = $parameters->getMainObject();
			$models = [];
			if ($model instanceof Model) {
				$models[] = $model;
			}
		}
		/** @noinspection PhpUnhandledExceptionInspection class */
		return Builder::create(Export::class)->export($models);
	}

}
