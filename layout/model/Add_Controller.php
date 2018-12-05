<?php
namespace ITRocks\Framework\Layout\Model;

use Exception;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Widget\Add;

/**
 * Layout model add controller : initialises pages
 */
class Add_Controller extends Add\Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 * @throws Exception
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		/** @noinspection PhpUnhandledExceptionInspection add */
		$model = $parameters->getMainObject();
		if (!$model->pages) {
			$model->pages = [
				new Page(Page::UNIQUE),
				new Page(Page::FIRST),
				new Page(Page::MIDDLE),
				new Page(Page::LAST),
				new Page(Page::ALL)
			];
		}

		return parent::run($parameters, $form, $files, $class_name);
	}

}
