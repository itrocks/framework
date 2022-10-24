<?php
namespace ITRocks\Framework\Dao\File\Session_File;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\File\Session_File;
use ITRocks\Framework\View;

/**
 * Session file image controller
 */
class Image_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$objects = $parameters->getObjects();
		$objects['link'] = SL . str_replace(BS, SL, Session_File::class) . '/output/' . reset($objects);
		return View::run($objects, $form, $files, Session_File::class, 'image');
	}

}
