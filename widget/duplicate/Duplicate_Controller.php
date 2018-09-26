<?php
namespace ITRocks\Framework\Widget\Duplicate;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Duplicator;
use ITRocks\Framework\Widget\Edit;

/**
 * Default duplicate controller
 *
 * Opens an edit form, filled with the data of an object, but without it's ids
 */
class Duplicate_Controller extends Edit\Controller
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
		return parent::getViewParameters($parameters, $form, $class_name);
	}

}
