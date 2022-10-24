<?php
namespace ITRocks\Framework\Layout\Model\Page;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Layout\Model\Page;
use ITRocks\Framework\Tools\Files\PDF;

/**
 * Layout model page background controller
 *
 * Display the background image (if set)
 */
class Background_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string The image data for display (headers are set too), or null if no image
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		/** @var $page Page */
		$page = $parameters->getMainObject();
		if ($page && $page->background) {
			$file_names = (new PDF($page->background->temporary_file_name))->toPng(150);
			$file_name  = reset($file_names);
			if ($file_name) {
				header('content-type: image/png');
				header('content-length: ' . filesize($file_name));
				return file_get_contents($file_name);
			}
		}
		return null;
	}

}
