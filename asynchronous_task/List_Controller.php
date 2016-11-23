<?php

namespace ITRocks\Framework\Asynchronous_Task;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;

/**
 */
class List_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param Parameters $parameters
	 * @param array      $form
	 * @param array      $files
	 * @param string     $class_name
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		$sort = Dao::sort([Dao::reverse('creation_date'), Dao::reverse('id')]);
		$elements = Dao::readAll($class_name, [$sort, Dao::limit(15)]);
		$parameters->set('elements', $elements);
		$parameters->set('title', Loc::tr(ucfirst(Names::classToDisplay($class_name))));
		$parameters->getMainObject();
		$parameters = $parameters->getRawParameters();
		return View::run($parameters, $form, $files, $class_name, 'list');
	}

}
