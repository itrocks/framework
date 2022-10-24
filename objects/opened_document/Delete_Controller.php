<?php
namespace ITRocks\Framework\Objects\Opened_Document;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Feature\Delete;
use ITRocks\Framework\Objects\Opened_Document;

/**
 * Opened document delete controller
 */
class Delete_Controller extends Delete\Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * The opened document delete controller closes the opened document
	 * It purges all old opened documents too
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files, string $class_name)
		: ?string
	{
		$objects = $parameters->getObjects();
		foreach ($objects as $object) {
			if (is_object($object) && !($object instanceof Opened_Document)) {
				$result = Opened_Document::closeObject(array_shift($objects));
				Opened_Document::purge();
				return $result;
			}
		}
		return parent::run($parameters, $form, $files, $class_name);
	}

}
