<?php
namespace ITRocks\Framework\Objects\Opened_Document;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Objects\Opened_Document;

/**
 * This controllers returns true if a document is opened, else false
 *
 * @example
 * /ITRocks/Framework/Objects/Opened_Document/isOpened/A/Class/Path/identifier
 */
class Is_Opened_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$objects = $parameters->getObjects();
		foreach ($objects as $object) {
			if (is_object($object) && !($object instanceof Opened_Document)) {
				return (bool)Opened_Document::openedObject($object);
			}
		}
		return false;
	}

}
