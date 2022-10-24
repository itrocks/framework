<?php
namespace ITRocks\Framework\Layout\Display_Model;

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
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		/** @var $model Print_Model */
		$model = $parameters->getMainObject();
		if (!$model->pages) {
			$pages = [];
			foreach (array_keys(Page::ORDERING) as $screen) {
				$pages[] = $model->newPage($screen);
			}
			$model->pages = $pages;
		}

		return parent::run($parameters, $form, $files, $class_name);
	}

}
