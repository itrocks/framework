<?php
namespace ITRocks\Framework\Layout\Print_Model;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Feature\Add;
use ITRocks\Framework\Layout\Print_Model;

/**
 * Layout model add controller : initialises pages
 */
class Add_Controller extends Add\Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		/** @var $model Print_Model */
		$model = $parameters->getMainObject();
		if (!$model->pages) {
			$model->pages = [
				$model->newPage(Page::UNIQUE),
				$model->newPage(Page::FIRST),
				$model->newPage(Page::MIDDLE),
				$model->newPage(Page::LAST),
				$model->newPage(Page::ALL)
			];
		}

		return parent::run($parameters, $form, $files, $class_name);
	}

}
