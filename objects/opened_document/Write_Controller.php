<?php
namespace ITRocks\Framework\Objects\Opened_Document;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Objects\Opened_Document;
use ITRocks\Framework\Feature\Write;

/**
 * Opened document write controller
 */
class Write_Controller extends Write\Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * The opened document write controller updates an already existing record
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return string
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		$objects = $parameters->getObjects();
		foreach ($objects as $object) {
			if (is_object($object) && !($object instanceof Opened_Document)) {
				return Opened_Document::keepObjectOpened(array_shift($objects));
			}
		}
		return parent::run($parameters, $form, $files, $class_name);
	}

}
