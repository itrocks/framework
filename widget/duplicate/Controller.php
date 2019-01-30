<?php
namespace ITRocks\Framework\Widget\Duplicate;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Duplicator;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Widget\Edit;

/**
 * Default duplicate controller
 *
 * Opens an edit form, filled with the data of an object, but without it's ids
 */
class Controller extends Edit\Controller
{

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return mixed[]
	 */
	protected function getViewParameters(Parameters $parameters, array $form, $class_name)
	{
		$object     = $parameters->getMainObject($class_name);
		$duplicator = new Duplicator();
		$duplicator->createDuplicate($object);
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters['title'] = Loc::tr('New', $class_name) . SP . $parameters['title'];
		return $parameters;
	}

}
