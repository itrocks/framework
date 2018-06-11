<?php
namespace ITRocks\Framework\Layout\Model;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Widget\Add;

/**
 * Layout model add controller : initialises pages
 */
class Add_Controller extends Add\Add_Controller
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
		$model = $parameters->getMainObject();
		if (!$model->pages) {
			$model->pages = [
				new Page(Page::FIRST),
				new Page(2),
				new Page(Page::MIDDLE),
				new Page(-2),
				new Page(Page::LAST)
			];
		}

		return parent::run($parameters, $form, $files, $class_name);
	}

}
