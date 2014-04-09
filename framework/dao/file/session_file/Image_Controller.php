<?php
namespace SAF\Framework\Dao\File\Session_File;

use SAF\Framework\Parameters;
use SAF\Framework\Dao\File\Session_File;
use SAF\Framework\Feature_Controller;
use SAF\Framework\View;

/**
 * Session file image controller
 */
class Session_File_Image_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$objects = $parameters->getObjects();
		$objects['link'] = '/Session_File/output/' . reset($objects);
		return View::run($objects, $form, $files, Session_File::class, 'image');
	}

}
